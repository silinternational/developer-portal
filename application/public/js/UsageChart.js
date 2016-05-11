/**
 * Set up a namespace object for this class.
 */
var ucns = ucns || {};

/**
 * Class to represent a usage chart and allow (re)creating it.
 * 
 * @param {Array.<number>} The Array of values for the X-axis.
 * @param {Object.<string, Array.<string>>} The collection of named Arrays of
 *     values for the Y-axis.
 * @param {Object.<string,*>} The collection of options to use for this
 *     UsageChart.
 * @returns {ob.UsageChart}
 */
ucns.UsageChart = function(xValues, yValuesArrays, options) {
  
  // Make sure we have any required dependencies.
  if ((typeof dtf === 'undefined') || 
      (typeof dtf.DateTimeFormatter === 'undefined')) {
    throw {
      name: 'MissingDependency',
      message: 'This class requires the dtf.DateTimeFormatter class, which ' +
               'does not seem to be available.'
    };
  }

  // **** Record the given values: **** //

  /** @type {Array.<number>} */
  this.xValues = xValues;

  /** @type {Object.<string, Array.<string>>} */
  this.yValuesArrays = yValuesArrays;
  
  
  // Set up a way to track the current chart width.
  this.currentWidth = 0;


  // If no options were provided, use an empty object.
  var opt = options || {};


  // **** Set up our attributes (using defaults when necessary): **** //
  
  this.autoAdjustingWidth = false;
  this.boundOnWindowResizeFunction = null;
  this.resizeChartDelayTimerID = null;

  /**
   * The list of colors (as CSS color values, such as '#3399FF') to use for the
   * lines on the chart. Ideally, it should contain the same number of entries
   * as the yValuesArrays has keys. If there are fewer colors than lines, the
   * colors will be reused.
   * @type {Array.<string>}
   */
  this.colors = opt.colors || ['#000000'];
  
  /**
   * The HTML id of the DOM Element that the chart should be created in.
   * @type {string}
   */
  this.chartId = opt.chartId || 'usage-chart';

  /**
   * The height (in pixels) of the entire DOM Element containing the chart.
   * @type {number}
   */
  this.height = opt.height || 320;

  /**
   * The name indicating the time interval represented by each step in the
   * X-axis (e.g. - 'day', 'hour', etc.).
   * @type {string}
   */
  this.intervalName = opt.intervalName || 'day';

  /**
   * The minimum value that we should ensure is visible on the Y-axis.
   * @type {number}
   */
  this.topYValueToShow = opt.topYValueToShow || 10;

  /**
   * The minimum width (in pixels) of the entire DOM Element containing the
   * chart.
   * @type {number}
   */
  this.minimumWidth = opt.minimumWidth || 460;
  
  
  // Get the target DOM Element.
  var dom = document.getElementById(this.chartId);
  if ( ! (dom instanceof Element)) {
    throw {
      name: 'MissingDomElement',
      message: 'No DOM Element was found with an id of "' + this.chartId + '".'
    };
  }
  /** @type {Element} */
  this.dom = dom;
  
  // Get the chart DOM Element's parent element.
  var parentDom = this.dom.parentElement;
  if ( ! (parentDom instanceof Element)) {
    throw {
      name: 'MissingDomElement',
      message: 'The "' + this.chartId + '" DOM Element does not seem to have ' +
               'a parent Element.'
    };
  }
  /** @type {Element} */
  this.parentDom = parentDom;
};


/**
 * Generate the usage chart (using the currently-set options).
 * 
 * @param {boolean} autoResize (Optional:) Whether to automatically adjust the
 *     width of the chart when the window size changes. If not provided, the
 *     current setting will be kept (with by default is false).
 */
ucns.UsageChart.prototype.generateChart = function(autoResize) {
  
  // Figure out what width we should use for the chart.
  var chartWidth = this.getTargetChartWidth();
  
  // Set the chart's DOM element to be the desired width and height.
  this.dom.style.width = chartWidth + 'px';
  this.dom.style.height = this.height + 'px';

  // Set up the chart and text formatting.
  var r = Raphael(this.chartId),
      txtattr = { font: "15px sans-serif" };

  // Assemble the Arrays (plural) of values for the X-axis, including one copy
  // of the set of X values for each set of Y values that we have, plus a
  // hidden set of data to force a minimum horizontal visible range.
  var xData = [];
  for (var entryName in this.yValuesArrays) {
    xData.push(this.xValues);
  }
  xData.push([this.xValues[0], this.xValues[0]]);

  // Assemble (and count) the Arrays (plural) of values for the Y-axis into a
  // simple Array (instead of an Object), then add a hidden set of data to
  // force a minimum vertical visible range.
  var yData = [];
  var numYArrays = 0;
  for (var entryName in this.yValuesArrays) {
    yData.push(this.yValuesArrays[entryName]);
    numYArrays += 1;
  }
  yData.push(0, this.topYValueToShow);

  // Create an array of colors that is as long as the number of lines we have
  // using the list of colors we have (repeating them if necessary), adding an
  // additional entry for hiding the set of data used to force a minimum
  // visible range on the chart.
  var colorData = [];
  var currentColorIndex = 0;
  for (var entryName in this.yValuesArrays) {
    colorData.push(this.colors[currentColorIndex % this.colors.length]);
    currentColorIndex += 1;
  }
  colorData.push('transparent');
  
  // Get the interval name in a way we can use in closures.
  var intervalName = this.intervalName;

  // Create the line chart.
  var lines = r.linechart(
    40, 10,   // (x, y) of the chart's top-left corner.
    (chartWidth - 50), // width of chart.
    (this.height - 40), // height of chart.
    xData,
    yData,
    {
       nostroke: false,
           axis: "0 0 1 1",
      axisxstep: this.xValues.length - 1,
      axisystep: 10,
         symbol: "circle",
         smooth: false,
         colors: colorData
    }
  ).hoverColumn(function () {

    this.tags = r.set();

    var date = new Date(parseInt(this.axis));
    var labelPrefix = dtf.DateTimeFormatter.showAs(date, intervalName) + ' = ';

    for (var i = 0; i < numYArrays; i++) {
      this.tags.push(
        r.tag(
          this.x,
          this.y[i],
          labelPrefix + this.values[i],
          180,
          7
        ).insertBefore(this).attr(
          [
            { fill: "#fff" },
            { fill: this.symbols[i].attr("fill") }
          ]
        )
      );
    }
  }, function () {
      this.tags && this.tags.remove();
  });
  r.text(10, Math.round(this.height / 2),
         'Requests / ' + this.intervalName).attr(txtattr).transform("r270");
  r.text((Math.round(chartWidth / 2) + 10), (this.height - 10), 
         'Time').attr(txtattr);
  lines.symbols.attr({ r: 4 });

  // Change the x-axis labels.
  var axisItems = lines.axis[0].text.items;
  for( var i = 0, l = axisItems.length; i < l; i++ ) {
     var date = new Date(parseInt(axisItems[i].attr("text")));
     axisItems[i].attr("text", dtf.DateTimeFormatter.showAs(date, this.intervalName));
  }
  
  // Record our new width.
  this.currentWidth = chartWidth;
  
  // If applicable, update whether we're auto-adjusting the width when
  // necessary.
  if (typeof autoResize !== 'undefined') {
    this.setAutoAdjustWidth(autoResize);
  }
};


/**
 * Figure out what width the chart should use.
 * 
 * @returns {number} The target width (in pixels).
 */
ucns.UsageChart.prototype.getTargetChartWidth = function() {
  
  var width = 0;

  // If the chart's parent DOM Element has a (non-zero) width specified...
  if (this.parentDom.clientWidth) {

    // Use that to calculate the desired chart width.
    width = this.parentDom.clientWidth - 1;
  }
  
  // Enforce any minimum width.
  if (width < this.minimumWidth) {
    width = this.minimumWidth;
  }
  
  // Return the resulting value.
  return width;
};


/**
 * When it matters (such as when auto-updating the chart width), this function
 * will be called when the window resizes.
 */
ucns.UsageChart.prototype.onWindowResize = function() {
  
  // Make sure this function was called properly.
  if ( ! (this instanceof ucns.UsageChart)) {
    throw {
      name: 'IncorrectContext',
      message: 'This function needs to be called in such a way that "this" ' +
               'refers to the instance of this UsageChart class.',
      toString: function() { return this.name + ': ' + this.message; }
    };
  }
  
  // If we already have a timer going (to avoid resizing the chart repeatedly
  // as the user is resizing the window), cancel it.
  if (this.resizeChartDelayTimerID !== null) {
    clearTimeout(this.resizeChartDelayTimerID);
  }

  // Start a fresh timer.
  var usageChart = this;
  this.resizeChartDelayTimerID = setTimeout(function() {

    // If this timer fires, resize the chart.
    usageChart.updateChartWidth();

  }, 300);
};


/**
 * Update whether we're automatically adjusting the chart width when necessary.
 * 
 * @param {boolean} enable Whether to enable auto-adjusting the width.
 */
ucns.UsageChart.prototype.setAutoAdjustWidth = function(enable) {
  
  // If we haven't yet bound a copy of the onWindowResize function, do so.
  if (this.boundOnWindowResizeFunction === null) {
    this.boundOnWindowResizeFunction = this.onWindowResize.bind(this);
  }
  
  // If told to auto-adjust...
  if (enable) {
    
    // If we're not yet doing so...
    if (this.autoAdjustingWidth !== true) {

      // Try to start listening for window resize events.
      if (window && window.addEventListener) {
        window.addEventListener('resize', this.boundOnWindowResizeFunction,
                                false);
        this.autoAdjustingWidth = true;
      }
    }
  }
  // Otherwise (i.e. - if told NOT to do so)...
  else {
    
    // If we are currently auto-adjusting...
    if (this.autoAdjustingWidth === true) {

      // Try to stop listening for window resize events.
      if (window && window.removeEventListener) {
        window.removeEventListener('resize', this.boundOnWindowResizeFunction,
                                   false);
        this.autoAdjustingWidth = false;
      }
    }
  }
};


/**
 * If necessary, regenerate the chart to fit the current target width.
 */
ucns.UsageChart.prototype.updateChartWidth = function() {
  
  // Get the width that we should now use for the chart.
  var newWidth = this.getTargetChartWidth();
  
  // If the new width is different from the previous width...
  if (newWidth !== this.currentWidth) {
    
    // Recreate the chart.
    this.generateChart();
  }
};
