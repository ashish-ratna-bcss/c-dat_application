<?php
/**
 * The page chrome the CDAT screens share: doctype, the Spry menu bar, and the
 * centred wrapper the content sits in.
 *
 * Several pages -- cellid_search, COMMON_CNTS, vehicle_search_criteria -- were
 * left as bare <body bgcolor> with no stylesheet and no menu when their .htm
 * half was removed, so they rendered as unstyled text with no way to navigate
 * anywhere. Rather than paste sixty lines of menu markup into each one, they
 * call these two functions.
 *
 * Usage:
 *   require_once __DIR__ . '/cdat_chrome.php';
 *   cdat_page_top('Cellid Search');
 *   ... the page's own form and results ...
 *   cdat_page_bottom();
 *
 * The menu is a copy of the one on ADDRESS.php, which is the version the
 * de-duplication settled on; links point at real filenames.
 */

function cdat_page_top(string $title): void
{
    $t = htmlspecialchars($title, ENT_QUOTES);
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $t ?></title>
<script src="SpryAssets/sprymenubar.js" type="text/javascript"></script>
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">
/* The results tables these pages echo are raw <table border=1> with inline
   font tags. This keeps them readable inside the centred layout instead of
   running off the side of it. */
.cdat-content{ width:1300px; margin:0 auto; text-align:left; overflow-x:auto; }
.cdat-content table{ border-collapse:collapse; }
.cdat-form{ background-color:#A9D1F5; padding:10px; margin:0 0 10px 0; }
.cdat-form input[type=text], .cdat-form select{ padding:3px 6px; margin:2px; }
.cdat-note{ background-color:#FFF3CD; color:#7A5B00; border:1px solid #E0C060;
            padding:8px 12px; margin:8px 0; font:bold 13px verdana; display:inline-block; }
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" border="2">
    <tr>
      <td align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="IMAGES/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
           <li><a href="home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.html">Summary Within a State</a></li>
                <li><a href="sum_out_state.php">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="movements.html"> MOVEMENTS </a></li>
                <li><a href="movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
                <li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
                <li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
                <li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.php">Phones used in Imei</a></li>
                <li><a href="imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.php">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
               <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="vehicle_search.php">Vehicle Search</a></li>
                <li><a href="vehicle_search_criteria.php">Vehicle Search Criteria</a></li>
                <li><a href="common_cnts.php">Common Cnts</a></li>
                <li><a href="admin_activity_log.php">User Activity</a></li>
                <li><a href="admin_sql_console.php">SQL Query Console</a></li>
                </ul>
            </li>
                </ul>
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <div class="cdat-content">
    <?php
}

function cdat_page_bottom(): void
{
    ?>
      </div>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
    <?php
}
