<?php
/* @var $this DashboardController */
/* @var $user User */
/* @var $keyRequests KeyRequest[] */
/* @var $usageStats UsageStats */
/* @var $apisOwnedByUser Api[]|null */
/* @var $currentInterval string */
/* @var $chart string */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard',
);

$this->pageTitle = 'Dashboard';

// If the user is an owner and has any pending key requests, say so.
if ($user->hasOwnerPrivileges()) {
    $numOwnerApis = 0;
    $numPendingKeyRequestsForOwnerApis = 0;
    foreach ($apisOwnedByUser as $api) {
        $numOwnerApis += 1;
        $numPendingKeyRequestsForOwnerApis += $api->pendingKeyCount;
    }

    if ($numPendingKeyRequestsForOwnerApis > 0) {
        echo sprintf(
            '<div class="row-fluid"><div class="span12 alert alert-error">'
            . '<button type="button" class="close" data-dismiss="alert">&times;'
            . '</button> <b>Important:</b> Your %s %s pending key request%s. '
            . '<a href="javascript: scrollToAnchor(\'apis\', \'.pending-keys.badge-important\');">See below</a> '
            . '(under "My APIs") for details.</div></div>',
            ($numOwnerApis === 1 ? 'API has' : 'APIs have'),
            $numPendingKeyRequestsForOwnerApis,
            ($numPendingKeyRequestsForOwnerApis === 1 ? '' : 's')
        );
    }
}

?>
<ul class="nav nav-tabs">
  <li<?php echo ($chart === \DashboardController::CHART_MY_KEYS ? ' class="active"' : ''); ?>>
    <a href="<?php echo $this->createUrl('', array(
        'interval' => $currentInterval,
        //'chart' => \DashboardController::CHART_MY_KEYS, // Automatic default.
    )); ?>">My Usage</a>
  </li>
  <?php

  if ($user->hasOwnerPrivileges()) {
      ?>
      <li<?php echo ($chart === \DashboardController::CHART_MY_APIS ? ' class="active"' : ''); ?>>
        <a href="<?php echo $this->createUrl('', array(
            'interval' => $currentInterval,
            'chart' => \DashboardController::CHART_MY_APIS,
        )); ?>">My APIs</a>
      </li>
      <?php
  }

  if ($user->isAdmin()) {
      ?>
      <li<?php echo ($chart === \DashboardController::CHART_ALL_APIS ? ' class="active"' : ''); ?>>
        <a href="<?php echo $this->createUrl('', array(
            'interval' => $currentInterval,
            'chart' => \DashboardController::CHART_ALL_APIS,
        )); ?>">All APIs</a>
      </li>
      <li<?php echo ($chart === \DashboardController::CHART_TOTALS ? ' class="active"' : ''); ?>>
        <a href="<?php echo $this->createUrl('', array(
            'interval' => $currentInterval,
            'chart' => \DashboardController::CHART_TOTALS,
        )); ?>">Totals</a>
      </li>
      <?php
  }

  ?>
</ul>
<div class="tab-content">
  <div class="text-center">
    <div class="btn-group shrink-below-480" style="margin: 5px auto;">
      <?php
      $validIntervals = \UsageStats::getValidIntervalNames();
      foreach ($validIntervals as $validInterval) {
          $isActive = ($validInterval === $currentInterval);
          echo sprintf(
              '<a class="btn%s" href="%s">%s</a>',
              ($isActive ? ' active' : ''),
              $this->createUrl('', array(
                  'interval' => $validInterval,
                  'chart' => $chart,
              )),
              CHtml::encode($validInterval)
          );
      }
      ?>
    </div>
  </div>
  <div id="ajax-usage-chart-destination" style="height: 368px; overflow: hidden;">
    <div style="height: 100%; text-align: center;">
      <p class="muted" style="margin-top: 160px;"><i>Loading usage chart...</i></p>
    </div>
  </div>
  <script type="text/javascript">
  $('#ajax-usage-chart-destination').load(
    '<?= $this->createUrl('dashboard/usage-chart', array(
           'interval' => $currentInterval,
           'chart' => $chart
         )); ?>',
    null,
    function(responseText, textStatus, jqXHR) {
      if (textStatus === 'success') {
        $(this).animate({
          'height': this.scrollHeight + 'px'
        }, 400, 'swing', function() {
          $(this).css('overflow', '');
          $(this).css('height', '');
        });
      } else {
        window.console.error(textStatus);
        $(this).html('<div class="alert alert-danger"></div>').children().text(
          'Oops! We were not able to get the usage chart. (' + responseText + ')'
        );
      }
    }
  );
  </script>
</div>

<div style="padding-top: 20px;">
  <h4><a name="keys"></a>My Keys <small>Your active keys and your requested keys</small></h4>
  <div class="container-fluid">
    <div class="row-fluid">
      <?php

      if (count($keyRequests) <= 0) {
          echo '<i>None</i>';
      }

      // Track when we should start a new row.
      $startNewRow = false;

      // For each Key/KeyRequest to show...
      foreach ($keyRequests as $keyRequest) {

        // Get this Key's/KeyRequet's API's display name.
        $displayName = $keyRequest->api->display_name;

        // Start the card, giving it a left border color specific to this
        // key's API.
        echo sprintf(
          '<div class="span6">'
          . '<div class="card-contents" style="border-left-color: %s">',
          CHtml::encode(UsageStats::getColorsForName($displayName))
        );

        // Figure out what details to show on the card (and what action
        // links, if applicable).
        $cardActionLinksHtml = null;
        $cardDetailsHtml = null;
        switch($keyRequest->status) {
          case KeyRequest::STATUS_APPROVED:
            if ($keyRequest->key !== null) {

              // If the key still exists, assemble the info/links to show
              // for this key/request/api.
              $cardActionLinksHtml = sprintf(
                '<div class="btn-group pull-right">'
                . '<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">'
                  . 'Actions <span class="caret"></span>'
                . '</button>'
                . '<ul class="dropdown-menu">'
                  . '<li>'
                    . '<a href="%s" class="nowrap space-after-icon">'
                      . '<i class="icon-list"></i>Key Details'
                    . '</a>'
                  . '</li> '
                  . '<li>'
                    . '<a href="%s" class="nowrap space-after-icon">'
                      . '<i class="icon-book"></i>API Documentation'
                    . '</a>'
                  . '</li>'
                  . '<li>'
                    . '<a href="%s" class="nowrap space-after-icon">'
                      . '<i class="icon-play-circle"></i>API Playground'
                    . '</a>'
                  . '</li> '
                . '</ul>'
                . '</div>',
                $this->createUrl('/key/details/', array(
                    'id' => $keyRequest->key->key_id,
                )),
                $this->createUrl('/api/details/', array(
                    'code' => $keyRequest->api->code,
                )),
                $this->createUrl('/api/playground/', array(
                  'key_id' => (int)$keyRequest->key->key_id
                ))
              );
              $cardDetailsHtml = sprintf(
                '<div class="card-url">'
                . '<span class="card-hover-content"><b>Domain: </b></span><u>%s</u>'
                . '</div> '
                . '<div class="card-summary">'
                . '<span class="card-hover-content"><b>Purpose: </b></span>%s'
                . '</div>',
                str_replace(
                  '.',
                  '.<wbr>',
                  CHtml::encode($keyRequest->domain)
                ),
                CHtml::encode($keyRequest->purpose)
              );

            } else {

              // Otherwise, say so.
              $cardDetailsHtml = '<div class="text-error">'
                  . '<span class="card-hover-content"><b>Status: </b></span>'
                  . '<i>Key was not found. It may have been revoked.</i>'
                  . '</div>';
            }
            break;

          case KeyRequest::STATUS_DENIED:
            $cardActionLinksHtml = LinksManager::generateActionsDropdownHtml(
                LinksManager::getDashboardKeyRequestActionLinks($keyRequest),
                LinksManager::BUTTON_SIZE_SMALL
            );
            $cardDetailsHtml = '<div class="text-error">'
              . '<span class="card-hover-content"><b>Status: </b></span>'
              . '<i>Key request denied. </i>'
              . '</div>';
            break;

          case KeyRequest::STATUS_PENDING:
            $cardActionLinksHtml = LinksManager::generateActionsDropdownHtml(
                LinksManager::getDashboardKeyRequestActionLinks($keyRequest),
                LinksManager::BUTTON_SIZE_SMALL
            );
            $cardDetailsHtml = '<div>'
              . '<span class="card-hover-content"><b>Status: </b></span>'
              . '<i>Key requested, waiting for approval. </i>'
              . '</div>';
            break;

          case KeyRequest::STATUS_REVOKED:
            $cardActionLinksHtml = LinksManager::generateActionsDropdownHtml(
                LinksManager::getDashboardKeyRequestActionLinks($keyRequest),
                LinksManager::BUTTON_SIZE_SMALL
            );
            $cardDetailsHtml = '<div class="text-error">'
              . '<span class="card-hover-content"><b>Status: </b></span>'
              . '<i><b>Key revoked. </b></i>'
              . '</div>';
            break;

          default:
            $cardDetailsHtml = '<div class="text-error">'
              . '<i><b>Error:</b> Unknown status ('
              . CHtml::encode($keyRequest->status) . ')</i>'
              . '</div>';
            break;
        }

        // If we have any action links to show on the card, show them.
        if ($cardActionLinksHtml !== null) {
          echo $cardActionLinksHtml;
        }

        // Show the name.
        echo sprintf(
          '<div class="card-title">'
          . '<span class="card-hover-content">API: </span>%s'
          . '</div>',
          CHtml::encode($displayName)
        );

        // If we have any details to show on the card, show them.
        if ($cardDetailsHtml !== null) {
          echo $cardDetailsHtml;
        }

        // End the card.
        echo '</div></div>';

        // Start a new row if appropriate.
        if ($startNewRow) {
          echo '</div><div class="row-fluid">';
        }
        $startNewRow = !$startNewRow;
      }

      ?>
    </div>
  </div>
</div>

<?php

if ($user->hasOwnerPrivileges()) {

    // Set up the table of APIs.
    $apisData = new CArrayDataProvider($apisOwnedByUser, array(
        'keyField' => false,
    ));
    $this->widget('bootstrap.widgets.TbGridView', array(
        'type' => 'striped hover',
        'dataProvider' => $apisData,
        'template' => sprintf(
            '<h4><a name="apis"></a>My APIs <small>%s</small></h4> {items}{pager}',
            'Click on the number of active or pending keys for more information.'
        ),
        'columns' => array(
            array('name' => 'display_name', 'header' => 'Name'),
            array(
                'header' => 'Internal API Endpoint',
                'value' => 'CHtml::encode($data->protocol . "://" . ' .
                           '$data->endpoint . ($data->default_path ?: ""))',
            ),
            array(
                'class' => 'CLinkColumn',
                'labelExpression' => 'sprintf('
                    . '"<span class=\"badge%s\" title=\"%s\">%s</span>",'
                    . '($data->keyCount ? " badge-info" : "" ),'
                    . '"Click for more information",'
                    . '$data->keyCount'
                . ')',
                'urlExpression' => '\Yii::app()->createUrl('
                    . '"api/active-keys", '
                    . 'array("code" => $data->code)'
                . ')',
                'header' => 'Active Keys',
                'headerHtmlOptions' => array(
                  'style' => 'text-align: center',
                ),
                'htmlOptions' => array(
                  'style' => 'text-align: center',
                ),
            ),
            array(
                'class' => 'CLinkColumn',
                'labelExpression' => 'sprintf('
                    . '"<span class=\"badge pending-keys%s\" title=\"%s\">%s</span>",'
                    . '($data->pendingKeyCount ? " badge-important" : "" ),'
                    . '"Click for more information",'
                    . '$data->pendingKeyCount'
                . ')',
                'urlExpression' => '\Yii::app()->createUrl('
                    . '"api/pending-keys", '
                    . 'array("code" => $data->code)'
                . ')',
                'header' => 'Pending Keys',
                'headerHtmlOptions' => array(
                  'style' => 'text-align: center',
                ),
                'htmlOptions' => array(
                  'style' => 'text-align: center',
                ),
            ),
            array(
                'class' => 'ActionLinksColumn',
                'htmlOptions' => array('style' => 'text-align: right'),
                'links' => array(
                    array(
                        'icon' => 'list',
                        'text' => 'API Details',
                        'urlPattern' => '/api/details/:code',
                    ),
                )
            ),
        ),
    ));
}

?>
<script type="text/javascript">

function scrollToAnchor(anchorName, opt_cssToFlash) {
    var aTag = $("a[name='"+ anchorName +"']");
    $('html,body').animate({scrollTop: aTag.offset().top}, 1000);
    if (opt_cssToFlash) {
        flashUiElement(opt_cssToFlash);
    }
}
function flashUiElement(cssSelector) {
    $(cssSelector).delay(1200).fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow');    
}

</script>
