/**
 * Set up a namespace object for this class.
 */
var dtf = dtf || {};

/**
 * Helper class for formatting date/time information in various ways.
 * 
 * @returns {ob.DateTimeFormatter}
 */
dtf.DateTimeFormatter = function() {
};


/**
 * Format the given date/time to communicate the given interval.
 * 
 * @param {Date} date The Date instance to be formatted.
 * @param {string} intervalName The name of the time interval to communicate.
 * @returns {string} A string representing the given date/time information.
 */
dtf.DateTimeFormatter.showAs = function(date, intervalName) {
  switch (intervalName) {
    case 'day':
      return dtf.DateTimeFormatter.showDay(date);
    case 'hour':
      return dtf.DateTimeFormatter.showHour(date);
    case 'minute':
      return dtf.DateTimeFormatter.showMinute(date);
    case 'second':
      return dtf.DateTimeFormatter.showSecond(date);
    default:
      return date.toString();
  }
};


/**
 * Format the given date/time to communicate the day.
 * 
 * @param {Date} date The Date instance to be formatted.
 * @returns {string} A string representing the given date/time information.
 */
dtf.DateTimeFormatter.showDay = function(date) {
  return (date.getMonth() + 1) + '/' + date.getDate();
};


/**
 * Format the given date/time to communicate the hour.
 * 
 * @param {Date} date The Date instance to be formatted.
 * @returns {string} A string representing the given date/time information.
 */
dtf.DateTimeFormatter.showHour = function(date) {
  return date.getHours() + ':00';
};


/**
 * Format the given date/time to communicate the minute.
 * 
 * @param {Date} date The Date instance to be formatted.
 * @returns {string} A string representing the given date/time information.
 */
dtf.DateTimeFormatter.showMinute = function(date) {
  
  // Get the minutes value.
  var minutes = date.getMinutes();
  
  // Left-pad it with a zero if necessary.
  var minuteString = String(minutes);
  if (minuteString.length < 2) {
    minuteString = '0' + minuteString;
  }
    
  // Finish assembling and return the string.
  return date.getHours() + ':' + minuteString;
};


/**
 * Format the given date/time to communicate the second.
 * 
 * @param {Date} date The Date instance to be formatted.
 * @returns {string} A string representing the given date/time information.
 */
dtf.DateTimeFormatter.showSecond = function(date) {
  
  // Get the minutes value.
  var minutes = date.getMinutes();
  
  // Left-pad it with a zero if necessary.
  var minuteString = String(minutes);
  if (minuteString.length < 2) {
    minuteString = '0' + minuteString;
  }
    
  // Get the seconds value.
  var seconds = date.getSeconds();
  
  // Left-pad it with a zero if necessary.
  var secondsString = String(seconds);
  if (secondsString.length < 2) {
    secondsString = '0' + secondsString;
  }
    
  // Finish assembling and return the string.
  return date.getHours() + ':' + minuteString + ':' + secondsString;
};
