#!/usr/bin/env python3
"""Generate TXT and PDF migration status report."""

import os
from datetime import datetime
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT

# ── Data ──────────────────────────────────────────────────────────────────────

COMPLETED = [
    # (Database, PG Table, Rows, MSSQL Source DB, MSSQL Source Table)
    ("IR_DB", "brief_facts",                            "17,635",    "mssql_dump_ir",       "BRIEF_FACTS"),
    ("IR_DB", "disposal_of_property",                   "14,427",    "mssql_dump_ir",       "DISPOSAL_OF_PROPERTY"),
    ("IR_DB", "family_history",                         "143,866",   "mssql_dump_ir",       "FAMILY_HISTORY"),
    ("IR_DB", "fingerprint_matched_undetected_cases_withimage", "53","mssql_dump_ir",       "FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE"),
    ("IR_DB", "habitual_offenders",                     "872",       "mssql_dump_ir",       "HABITUAL_OFFENDERS"),
    ("IR_DB", "image_table",                            "15,272",    "mssql_dump_ir",       "IMAGE_TABLE"),
    ("IR_DB", "ir_particulars",                         "17,111",    "mssql_dump_ir",       "IR_PARTICULARS"),
    ("IR_DB", "local_contacts_facilitators",            "26,546",    "mssql_dump_ir",       "LOCAL_CONTACTS_FACILITATORS"),
    ("IR_DB", "offence_details",                        "17,309",    "mssql_dump_ir",       "OFFENCE_DETAILS"),
    ("IR_DB", "previous_offence_details",               "29,464",    "mssql_dump_ir",       "PREVIOUS_OFFENCE_DETAILS"),
    ("IR_DB", "relationship_with_other_associates",     "37,816",    "mssql_dump_ir",       "RELATIONSHIP_WITH_OTHER_ASSOCIATES"),
    ("JRMS_DB", "jrms_total_2012_to_2017",              "93,119",    "mssql_dump_jrms",     "JRMS_TOTAL"),
    ("PDACT_DB", "pdact_main_table",                    "1,205",     "mssql_dump_pdact",    "PDACT_MAIN_TABLE"),
    ("ROWDY_SHEETS_DB", "rowdy_sheeter_complete_data",  "109,186",   "mssql_dump_ir",       "ROWDY SHEETERS TO CHECK"),
    ("CDATDUPL_DB", "cdat_civilsupply",                 "42,459,644","mssql_dump_cdatdupl", "CDAT_CIVILSUPPLY"),
    ("CDATDUPL_DB", "cdat_gas_details",                 "11,256,072","mssql_dump_cdatdupl", "CDAT_GAS_DETAILS"),
    ("CDATDUPL_DB", "cdat_licence",                     "13,631,832","distributed_db",      "dl_data"),
    ("CDATDUPL_DB", "cdat_passport",                    "453,175",   "mssql_dump_cdatdupl", "CDAT_PASSPORT"),
    ("CDATDUPL_DB", "cdat_provider_master",             "15",        "old postgres DB",     "cdat_provider_master"),
    ("CDATDUPL_DB", "cdat_rta",                         "21,170,482","distributed_db",      "rta_data"),
    ("CDATDUPL_DB", "cdat_state_master",                "52",        "old postgres DB",     "cdat_state_master"),
    ("CDATDUPL_DB", "cdatpcsuspect",                    "998",       "mssql_dump_cdatdupl", "CDATPCSUSPECT (sample only)"),
    ("CDATDUPL_DB", "cdatcelltowerareanew",             "75,221,616","cellids_db",          "CELLIDS"),
    ("CDATDUPL_DB", "cdatphonearea",                    "33,759",    "mssql_dump_cdatdupl", "CDATPHONEAREA"),
    ("CDATDUPL_DB", "cdatsuspect",                      "91,992",    "mssql_dump_cdatdupl", "CDATSUSPECT"),
    ("CDATDUPL_DB", "complete_mo_classification",       "7,646",     "mssql_dump_cdatdupl", "COMPLETE_MO_CLASSIFICATION"),
    ("CDATDUPL_DB", "mcc_mnc",                          "195",       "mssql_dump_cdatdupl", "MCC_MNC"),
    ("CDATDUPL_DB", "mnc_codes",                        "340",       "mssql_dump_cdatdupl", "MNC_CODES"),
    ("CDATDUPL_DB", "mo_image_table",                   "2,514",     "mssql_dump_cdatdupl", "MO_IMAGE_TABLE"),
]

IN_PROGRESS = []

PENDING_NO_SOURCE = [
    ("CDATDUPL_DB", "ndps_abstract_1",       "No MSSQL source found in any of the 4 dumps — ask team"),
    ("CDATDUPL_DB", "rowdy_sheeter_data1",   "No MSSQL source found (different from rowdy_sheeter_complete_data)"),
    ("CDATDUPL_DB", "suspect_image_table",   "No MSSQL source found in any of the 4 dumps — ask team"),
]

PENDING_STORAGE = [
    (
        "CDATDUPL_DB",
        "cdatpcsuspect (full CDR)",
        "~1.05B rows / ~430 GB PG",
        "HYD_UNIT_CDAT",
        "Full call records — not in 4 dumps. HYD_UNIT_CDATPCSUSPECT.BAK never restored. Deferred: disk space.",
    ),
    ("CDATDUPL_DB", "address_other_state", "1.9 TB", "address_db (MSSQL)", "Needs ~2 TB free space"),
    ("CDATDUPL_DB", "cdataddress",         "195 GB",  "address_db (MSSQL)", "Needs ~200 GB free space"),
]

HYD_UNIT_PCSUSPECT_NOTES = [
    "cdatpcsuspect in Postgres currently has only 998 rows — copied from mssql_dump_cdatdupl (CDATDUPL2 backup).",
    "That dump holds a small suspect/sample table, NOT the full Call Detail Record (CDR) data.",
    "Full CDR call logs live in MSSQL database HYD_UNIT_CDAT (~1.05 billion rows, ~222 GB on disk).",
    "The separate backup HYD_UNIT_CDATPCSUSPECT_22042026.BAK was never restored as mssql_dump_cdr.",
    "Full cdatpcsuspect migration was intentionally deferred: Postgres needs ~430 GB but /mnt/storage1 had ~350 GB free.",
    "To migrate later: copy from HYD_UNIT_CDAT using scripts/migrate_cdr.sh or migrate_copy.py cdr job.",
]

GENERATED_AT = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
OUT_DIR = os.path.dirname(os.path.abspath(__file__))
TXT_PATH = os.path.join(OUT_DIR, "migration_status_report.txt")
PDF_PATH = os.path.join(OUT_DIR, "migration_status_report.pdf")

# ── TXT ───────────────────────────────────────────────────────────────────────

def write_txt():
    lines = []
    SEP = "=" * 100
    SEP2 = "-" * 100

    lines.append(SEP)
    lines.append("  MSSQL → PostgreSQL Migration Status Report")
    lines.append(f"  Generated: {GENERATED_AT}")
    lines.append(SEP)
    lines.append("")

    # Completed
    lines.append("1. COMPLETED TABLES")
    lines.append(SEP2)
    lines.append(f"{'Database':<22} {'PG Table':<52} {'Rows':>15}  {'Source DB':<25} {'Source Table'}")
    lines.append(SEP2)
    for db, tbl, rows, src_db, src_tbl in COMPLETED:
        lines.append(f"{db:<22} {tbl:<52} {rows:>15}  {src_db:<25} {src_tbl}")
    lines.append("")

    # In Progress
    lines.append("2. IN PROGRESS")
    lines.append(SEP2)
    lines.append(f"{'Database':<22} {'Table':<35} {'Progress':<30} {'Source':<35} {'ETA'}")
    lines.append(SEP2)
    for db, tbl, prog, src, eta in IN_PROGRESS:
        lines.append(f"{db:<22} {tbl:<35} {prog:<30} {src:<35} {eta}")
    lines.append("")

    # Pending — no source
    lines.append("3. PENDING — NO SOURCE IDENTIFIED (Team Input Required)")
    lines.append(SEP2)
    lines.append(f"{'Database':<22} {'Table':<35} {'Issue'}")
    lines.append(SEP2)
    for db, tbl, issue in PENDING_NO_SOURCE:
        lines.append(f"{db:<22} {tbl:<35} {issue}")
    lines.append("")

    # Pending — storage
    lines.append("4. PENDING — STORAGE BLOCKED (Large Tables)")
    lines.append(SEP2)
    lines.append(f"{'Database':<22} {'Table':<25} {'Size':>8}  {'MSSQL Source':<25} {'Note'}")
    lines.append(SEP2)
    for db, tbl, sz, src, note in PENDING_STORAGE:
        lines.append(f"{db:<22} {tbl:<25} {sz:>8}  {src:<25} {note}")
    lines.append("")

    # HYD_UNIT pcsuspect explanation
    lines.append("5. WHY HYD_UNIT PCSUSPECT (FULL CDR) IS MISSING")
    lines.append(SEP2)
    for note in HYD_UNIT_PCSUSPECT_NOTES:
        lines.append(f"  • {note}")
    lines.append("")

    # Summary
    lines.append("6. SUMMARY")
    lines.append(SEP2)
    lines.append(f"  Completed tables  : {len(COMPLETED)}")
    lines.append(f"  In progress       : {len(IN_PROGRESS)}")
    lines.append(f"  Missing source    : {len(PENDING_NO_SOURCE)}  (need team input)")
    lines.append(f"  Storage blocked   : {len(PENDING_STORAGE)}  (includes full cdatpcsuspect + address tables)")
    lines.append("")
    lines.append(SEP)

    with open(TXT_PATH, "w") as f:
        f.write("\n".join(lines) + "\n")
    print(f"TXT written: {TXT_PATH}")


# ── PDF ───────────────────────────────────────────────────────────────────────

def write_pdf():
    doc = SimpleDocTemplate(
        PDF_PATH,
        pagesize=A4,
        leftMargin=1.5 * cm,
        rightMargin=1.5 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
    )
    styles = getSampleStyleSheet()
    title_style = ParagraphStyle("title", fontSize=16, alignment=TA_CENTER,
                                  fontName="Helvetica-Bold", spaceAfter=4)
    sub_style   = ParagraphStyle("sub",   fontSize=9,  alignment=TA_CENTER,
                                  fontName="Helvetica", spaceAfter=14, textColor=colors.grey)
    h1_style    = ParagraphStyle("h1",    fontSize=11, fontName="Helvetica-Bold",
                                  spaceAfter=4, spaceBefore=12,
                                  textColor=colors.HexColor("#1a3a5c"))
    note_style  = ParagraphStyle("note",  fontSize=8,  fontName="Helvetica-Oblique",
                                  textColor=colors.HexColor("#555555"), spaceAfter=8)

    GREEN  = colors.HexColor("#d4edda")
    ORANGE = colors.HexColor("#fff3cd")
    RED    = colors.HexColor("#f8d7da")
    BLUE   = colors.HexColor("#d1ecf1")
    HDR    = colors.HexColor("#1a3a5c")
    WHITE  = colors.white
    LIGHT  = colors.HexColor("#f2f5f9")

    def make_table(data, col_widths, row_colors=None):
        t = Table(data, colWidths=col_widths, repeatRows=1)
        style_cmds = [
            ("BACKGROUND", (0, 0), (-1, 0), HDR),
            ("TEXTCOLOR",  (0, 0), (-1, 0), WHITE),
            ("FONTNAME",   (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",   (0, 0), (-1, 0), 8),
            ("FONTNAME",   (0, 1), (-1, -1), "Helvetica"),
            ("FONTSIZE",   (0, 1), (-1, -1), 7.5),
            ("ROWBACKGROUNDS", (0, 1), (-1, -1), [WHITE, LIGHT]),
            ("GRID",       (0, 0), (-1, -1), 0.3, colors.HexColor("#cccccc")),
            ("VALIGN",     (0, 0), (-1, -1), "MIDDLE"),
            ("TOPPADDING", (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 5),
        ]
        if row_colors:
            for row_idx, bg in row_colors:
                style_cmds.append(("BACKGROUND", (0, row_idx), (-1, row_idx), bg))
        t.setStyle(TableStyle(style_cmds))
        return t

    story = []

    story.append(Paragraph("MSSQL → PostgreSQL Migration Status Report", title_style))
    story.append(Paragraph(f"Generated: {GENERATED_AT} &nbsp;|&nbsp; Project: c-dat_application", sub_style))
    story.append(HRFlowable(width="100%", thickness=1, color=HDR))
    story.append(Spacer(1, 10))

    # ── Section 1: Completed
    story.append(Paragraph("1. Completed Tables", h1_style))
    hdr = [["Database", "PG Table", "Rows Copied", "Source DB", "Source Table"]]
    rows = [[db, tbl, rows, src_db, src_tbl] for db, tbl, rows, src_db, src_tbl in COMPLETED]
    story.append(make_table(hdr + rows, [3*cm, 5.5*cm, 2.8*cm, 3.8*cm, 4.4*cm]))
    story.append(Paragraph(f"Total: {len(COMPLETED)} tables completed.", note_style))

    # ── Section 2: In Progress
    story.append(Paragraph("2. In Progress", h1_style))
    if IN_PROGRESS:
        hdr2 = [["Database", "Table", "Progress", "Source", "ETA"]]
        rows2 = [[db, tbl, prog, src, eta] for db, tbl, prog, src, eta in IN_PROGRESS]
        t2 = make_table(hdr2 + rows2, [3*cm, 4.5*cm, 4*cm, 4*cm, 3*cm],
                        row_colors=[(i+1, ORANGE) for i in range(len(rows2))])
        story.append(t2)
    else:
        story.append(Paragraph("None — all planned dump migrations finished.", note_style))

    # ── Section 3: Pending — no source
    story.append(Paragraph("3. Pending — No Source Identified (Team Input Required)", h1_style))
    hdr3 = [["Database", "Table", "Issue / Action Required"]]
    rows3 = [[db, tbl, issue] for db, tbl, issue in PENDING_NO_SOURCE]
    t3 = make_table(hdr3 + rows3, [3.5*cm, 5*cm, 10*cm],
                    row_colors=[(i+1, RED) for i in range(len(rows3))])
    story.append(t3)

    # ── Section 4: Storage blocked
    story.append(Paragraph("4. Pending — Storage Blocked (Large Tables)", h1_style))
    hdr4 = [["Database", "Table", "Size", "MSSQL Source", "Note"]]
    rows4 = [[db, tbl, sz, src, note] for db, tbl, sz, src, note in PENDING_STORAGE]
    t4 = make_table(hdr4 + rows4, [3.5*cm, 4*cm, 2*cm, 4.5*cm, 4.5*cm],
                    row_colors=[(i+1, BLUE) for i in range(len(rows4))])
    story.append(t4)

    # ── Section 5: HYD_UNIT pcsuspect
    story.append(Paragraph("5. Why HYD_UNIT PCSUSpect (Full CDR) Is Missing", h1_style))
    for note in HYD_UNIT_PCSUSPECT_NOTES:
        story.append(Paragraph(f"• {note}", note_style))

    # ── Section 6: Summary
    story.append(Spacer(1, 12))
    story.append(HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#cccccc")))
    story.append(Paragraph("6. Summary", h1_style))
    sum_data = [
        ["Status",              "Count", "Details"],
        ["✅ Completed",        str(len(COMPLETED)),       "Data copied and verified"],
        ["⏳ In Progress",      str(len(IN_PROGRESS)),     "None"],
        ["❓ Missing Source",   str(len(PENDING_NO_SOURCE)),"Ask team for source dump"],
        ["⛔ Storage Blocked",  str(len(PENDING_STORAGE)), "Full cdatpcsuspect + address tables"],
    ]
    row_colors_sum = [
        (1, GREEN), (2, ORANGE), (3, RED), (4, BLUE)
    ]
    story.append(make_table(sum_data, [5*cm, 3*cm, 10.5*cm], row_colors=row_colors_sum))

    doc.build(story)
    print(f"PDF written: {PDF_PATH}")


if __name__ == "__main__":
    write_txt()
    write_pdf()
