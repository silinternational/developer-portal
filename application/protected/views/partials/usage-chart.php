<?php
/* @var $chartDivId string */
/* @var $height number */
/* @var $minimumWidth number */
/* @var $xAxisValues array */
/* @var $yAxisValuesArrays array */
/* @var $topYValueToShow array */
/* @var $intervalName string */
/* @var $colors array */

?>
<script type="text/javascript" src="/js/raphael_g.raphael_g.line.js"></script>
<script type="text/javascript" src="/js/DateTimeFormatter.js"></script>
<div class="usage-chart-container">
    <div id="<?php echo CHtml::encode($chartDivId); ?>" 
         style="height: <?php echo (int)$height; ?>px; overflow: hidden; min-width: <?php echo (int)$minimumWidth; ?>px;"></div>
    <div id="<?php echo CHtml::encode($chartDivId); ?>-legend"
         style="min-width: <?php echo (int)$minimumWidth; ?>px;">
        <div style="margin-left: 40px; text-align: center;">
            <?php
            if ( ! $hasData) {
                ?>
                <div class="alert alert-info" style="margin: 0 0 -10px;">
                  <i>There is no usage information to show.</i>
                </div>
                <?php
            } else {
                ?>
                <b>Legend: </b>
                <?php

                // Show the names of the various lines.
                foreach ($yAxisValuesArrays as $displayName => $yAxisValues) {
                    echo sprintf(
                        '<div class="%s" style="border-color: #%s;">%s</div> ',
                        'chart-line-label',
                        substr(md5($displayName), -6),
                        CHtml::encode($displayName)
                    );
                }
            }
            ?>
        </div>
    </div>
</div>
<?php

// If showing daily totals, warn about UTC offsets.
if ($intervalName === \UsageStats::INTERVAL_DAY) {
    ?>
    <div class="alert alert-block">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h4>Note</h4>
        These numbers reflect totals from midnight-to-midnight UTC time, which
        may be different from midnight in your local timezone.<br />
        <br />
        For example, if your timezone is 5 hours behind UTC, then these numbers
        represent hits from 7&nbsp;pm on the specified day to 7&nbsp;pm on the
        following day in your timezone.
    </div>
    <?php
}

?>
<script type="text/javascript" src="/js/polyfill-bind.min.js"></script>
<script type="text/javascript" src="/js/UsageChart.js"></script>
<script type="text/javascript">

// Once everything is loaded, show the usage chart.
$(function() {
    var usageChart = new ucns.UsageChart(
        <?php echo json_encode($xAxisValues); ?>,
        <?php echo json_encode($yAxisValuesArrays); ?>,
        {
            colors: <?php echo json_encode($colors); ?>,
            chartId: '<?php echo $chartDivId; ?>',
            height: <?php echo (int)$height; ?>,
            intervalName: '<?php echo $intervalName; ?>',
            topYValueToShow: <?php echo (int)$topYValueToShow; ?>,
            minimumWidth: <?php echo (int)$minimumWidth; ?>
        }
    );
    usageChart.generateChart(true);
});

</script>
