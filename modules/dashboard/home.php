<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/quick_links.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Dashboard');
cdat_sum_page_open('sum-dashboard');
?>
<section class="sum-dash-hero mb-4 pb-3 border-bottom" aria-label="Dashboard">
    <div class="sum-dash-hero__head d-flex align-items-start gap-3 mb-3">
        <div class="sum-dash-hero__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12h4l2-7 4 14 2-7h6"/>
            </svg>
        </div>
        <div>
            <h1 class="sum-dash-hero__title">Call Data Analysis Tool</h1>
            <p class="sum-dash-hero__desc">Hyderabad City Police — quick access to your pinned pages.</p>
        </div>
    </div>

    <div class="sum-dash-notices" role="region" aria-label="Notices">
        <div class="sum-notice-bar" role="note">
            Please mail raw data to <strong>cdranalysiswing@gmail.com</strong> to view reports.
        </div>
        <div class="sum-notice-bar sum-notice-bar--alt" role="note">
            Mail to <strong>natgrid-hyd@tspolice.gov.in</strong> for Suspect Image search.
        </div>
    </div>
</section>

<section class="sum-dash-links">
    <?php cdat_ql_render_grid(); ?>
</section>

<?php
cdat_sum_page_close();
layout_end();
