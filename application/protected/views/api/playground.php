 <?php
/* @var $this ApiController */
/* @var $keyId integer|string */
/* @var $method string */
/* @var $apiOptions array */
/* @var $params array[] */
/* @var $path string */
/* @var $responseBody string */
/* @var $responseHeaders string */
/* @var $requestedUrl string */
/* @var $rawApiRequest string */
/* @var $responseSyntax string */
/* @var $debugText string */
/* @var $currentUser \User */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'API Playground',
);

$this->pageTitle = 'API Playground (beta)';
$this->pageSubtitle = 'A place to try things out';

?>
<div class="row-fluid">
    <div class="span12">
        <p>
            You can use this API Playground to test out calls to APIs and view the results.
            Be careful though of your calls: if you are testing against a production API, 
            you could have consequences you did not want.
        </p>
        <p>
            Simply choose which API you want to test (the list is limited to APIs for
            which you have keys), then choose the HTTP request method, enter the path 
            with any query string parameters needed for GET or add parameters and values
            for POST/PUT requests, and submit. 
        </p>
        <hr />
    </div>
</div>
<?php
echo CHtml::form(array('/api/playground/'), 'post');
?>
<div class="row-fluid">
    <div class="span12">
        <h4>Choose method, choose API, and enter the path</h4>
    </div>
</div>
<div class="row-fluid">
    <div class="span2">
        <b>Method</b><br />
        <select name="method" class="input-block-level">
            <option value="GET" 
                <?php if(isset($method) && $method == 'GET'){ 
                echo "selected='selected'";} ?>>GET</option>
            <option value="POST" 
                <?php if(isset($method) && $method == 'POST'){ 
                echo "selected='selected'";} ?>>POST</option>
            <option value="PUT" 
                <?php if(isset($method) && $method == 'PUT'){ 
                echo "selected='selected'";} ?>>PUT</option>
            <option value="DELETE" 
                <?php if(isset($method) && $method == 'DELETE'){ 
                echo "selected='selected'";} ?>>DELETE</option>
        </select>
    </div>
    <div class="span4">
        <b>API</b><br />
        <select name="key_id" class="input-block-level">
            <?php
                foreach($apiOptions as $apiOption){
                    echo "<option value='".$apiOption->key_id."' ";
                    if (isset($keyId) && $keyId == $apiOption->key_id) {
                        echo "selected='selected'";
                    }
                    echo ">".$apiOption->api->display_name."</option>".PHP_EOL;
                }
            ?>
        </select>
    </div>
    <div class="span6">
        <b>Path</b><br />
        <input type="text" class="input-block-level" name="path" 
               value="<?= \CHtml::encode($path); ?>"
               placeholder="/optional" />
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
        <h4>Add request parameters</h4>
    </div>
</div>
<div class="row-fluid">
    <div class="span9">
        <table class="table-borderless">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody id="request-params-table">
                <?php
                    $index = 0;
                    foreach($params as $param){
                ?>
                <tr>
                    <td>
                        <select name="param[<?php echo $index; ?>][type]" style="width: 80px;">
                            <option value="form" <?php if($param['type'] == 'form'){ echo "selected='selected'"; } ?>>
                                Form
                            </option>
                            <option value="header" <?php if($param['type'] == 'header'){ echo "selected='selected'"; } ?>>
                                Header
                            </option>
                        </select>
                    </td>
                    <td>
                        <input name="param[<?php echo $index; ?>][name]" class="input-large" type="text"
                           value="<?php echo CHtml::encode($param['name']); ?>" />
                    </td>
                    <td>
                        <input name="param[<?php echo $index; ?>][value]" class="input-large" type="text"
                           value="<?php echo CHtml::encode($param['value']); ?>" />
                    </td>
                </tr>
                <?php
                        $index++;
                    }
                ?>
            </tbody>
        </table>
        <a class="btn btn-default btn-small" href="javascript:addParameter()">Add Parameter</a>
    </div>
    <div class="span3" style="padding-top: 30px;">
        <label class="checkbox">
            <input type="checkbox" name="download" value="true"> Download results instead of displaying them
        </label>
        <button type="submit" class="btn btn-primary">
            Submit Request
        </button>
    </div>
</div>
</form>
<?php
if($responseBody){
?>
<div class="row-fluid">
    <div class="span12">
        <?php if ($currentUser->isAdmin()): ?>
            <h4 class="muted" title="Only shown to admins">Debug*:</h4>
            <pre style="overflow: auto;"><code class="language-http"><?php
                echo CHtml::encode($debugText);
            ?></code></pre>
        <?php endif; ?>
        
        <h4>Requested URL:</h4>
        <pre style="overflow: auto;"><code class="language-http"><?= \CHtml::encode($requestedUrl); ?></code></pre>
        
        <h4>Raw Request:</h4>
        <pre style="overflow: auto;"><code class="language-http"><?= \CHtml::encode($rawApiRequest); ?></code></pre>
        
        <h4>Response Headers:</h4>
        <pre style="overflow: auto;"><code class="language-http"><?= \CHtml::encode($responseHeaders); ?></code></pre>
        
        <h4>Response Body:</h4>
        <pre style="overflow: auto; max-height: 400px;"><code class="language-<?php echo $responseSyntax; ?>"><?php echo CHtml::encode($responseBody); ?></code></pre>
    </div>
</div>
<?php
}
?>
<script type="text/javascript">
    // Initialize index count
    var i = <?php echo count($params); ?>;

    // Function adds a row to the request parameters table
    function addParameter()
    {
      var row = "<tr>";
      row += "<td><select name='param["+i+"][type]' style='width: 80px;'><option value='form'>Form</option><option value='header'>Header</option></select></td>";
      row += "<td><input type='text' class='input-large' name='param["+i+"][name]' /></td>";
      row += "<td><input type='text' class='input-large' name='param["+i+"][value]' /></td>";
      row += "</tr>";
      $('#request-params-table').append(row);
      i++;
    }
</script>
