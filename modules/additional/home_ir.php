<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('IR Home');
cdat_sum_page_open('sum-dashboard');
?>
<section class="sum-dash-hero" aria-label="Interrogation Reports">
    <div class="sum-dash-hero__head">
        <div class="sum-dash-hero__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
            </svg>
        </div>
        <div>
            <h1 class="sum-dash-hero__title">Interrogation Reports</h1>
            <p class="sum-dash-hero__desc">Open IR forms and search from the sidebar, or use your pinned pages below.</p>
        </div>
    </div>
    <div class="sum-dash-notices" role="region" aria-label="Notices">
        <div class="sum-notice-bar" role="note">
            If you have suggestions or changes, please share them with Analysis Wing.
        </div>
    </div>
</section>
<section class="sum-dash-links">
    <?php cdat_ql_render_grid(); ?>
</section>
<?php
cdat_sum_page_close();
layout_end();
