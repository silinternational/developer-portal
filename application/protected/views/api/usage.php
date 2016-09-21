<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $api \Sil\DevPortal\models\Api */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'API Usage',
);

$this->pageTitle = 'API Usage';

// Include the necessary JavaScript files for the gRaphael line chart.
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . 
                        '/js/raphael_g.raphael_g.line.js');

?>
<dl class="dl-horizontal">
    <dt><?php echo CHtml::encode($api->code); ?></dt>
    <dd><?php echo CHtml::encode($api->display_name); ?></dd>
</dl>
<div class="row">
    <div class="span10 offset1">
        <p>
            Below is your daily and monthly usage information for this API.
        </p>
        <div class="alert alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Warning!</h4>
            Below this point is all still DUMMY data.
        </div>
    </div>
</div>
<div class="row">
    <div class="span8 offset1">
        <h3>Daily</h3>
        <div id="usage-chart" 
             style="height: 320px; overflow: hidden; width: 460px;"></div>
        <script type="text/javascript">

            // Collect the x-values.
            var xAxisValues = [];
            for( var i = 27; i >= 0; i-- ) {
              var date = new Date();
              date.setDate(date.getDate() - i);
              xAxisValues.push(date.getTime());
            }

            // Collect the y-values.
            var yAxisValues = [ 7, 22, 23, 18, 17, 20, 12,
                                9, 18, 22, 20, 19, 13, 10,
                                8, 18, 22, 19, 15, 16, 10,
                                8, 16, 15, 17, 18, 19,  6];

            // Set up the chart and text formatting.
            var r = Raphael("usage-chart"),
                txtattr = { font: "15px sans-serif" };

            // Create the line chart.
            var lines = r.linechart(
                30, 10,   // (x, y) of the chart's top-left corner.
                420, 280, // width, height of chart.
                [
                    xAxisValues, // x-values.
                    [xAxisValues[0], xAxisValues[0]] // Hidden data to force visible range.
                ],
                [
                    yAxisValues, // y-values.
                    [0, 50] // Hidden data to force visible range.
                ],
                {
                    nostroke: false, 
                        axis: "0 0 1 1", 
                   axisxstep: 27,
                   axisystep: 10,
                      symbol: "circle",
                      smooth: true,
                      colors: [
                          "#005B99",    // Color for the line.
                          "transparent" // Invisible zero-point.
                        ]
                }
            ).hoverColumn(function () {

                if (this.values[0] > 0) {
                    this.tags = r.set();

                    this.tags.push(
                        r.tag(
                            this.x,
                            this.y[0],
                            this.values[0],
                            180,
                            7
                        ).insertBefore(this).attr(
                            [
                                { fill: "#fff" },
                                { fill: this.symbols[0].attr("fill") }
                            ]
                        )
                    );
                }
            }, function () {
                this.tags && this.tags.remove();
            });
            r.text(10, 160, 
                   "Requests / day").attr(txtattr).transform("r270");
            r.text(240, 310, 
                   "Date").attr(txtattr);
            lines.symbols.attr({ r: 4 });

            // Change the x-axis labels.
            var axisItems = lines.axis[0].text.items
            for( var i = 0, l = axisItems.length; i < l; i++ ) {
               var date = new Date(parseInt(axisItems[i].attr("text")));
               // using the excellent dateFormat code from Steve Levithan
               axisItems[i].attr("text", (date.getMonth()+1) + '/' +
                                         date.getDate());
            }
        </script>
    </div>
    <div class="span2">
        <h3>Monthly</h3>
        <table class="table table-striped">
            <tr>
                <th>May</th>
                <td>437</td>
            </tr>
            <tr>
                <th>Apr</th>
                <td>462</td>
            </tr>
            <tr>
                <th>Mar</th>
                <td>435</td>
            </tr>
            <tr>
                <th>Feb</th>
                <td>394</td>
            </tr>
            <tr>
                <th>Jan</th>
                <td>411</td>
            </tr>
        </table>
    </div>
</div>
