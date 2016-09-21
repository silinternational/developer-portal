<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column1';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array('Home' => '/');
    
    /**
     * @var string|null The (optional) subtitle for the page.
     */
    public $pageSubtitle = null;

    /**
     * @var boolean Whether the subtitle string for this page is HTML (if true)
     *     or should be HTML-encoded (if false).
     */
    public $pageSubtitleIsHtml = false;

    /**
     * Generate the HTML for showing the page title (and subtitle, if
     * applicable).
     * 
     * @return string The resulting HTML (or an empty string if no title is
     *     available).
     */
    public function generatePageTitleHtml()
    {
        if ( ! $this->pageTitle) {
            return '';
        }
        
        return sprintf(
            '<h2 class="page-title">%s%s</h2>',
            CHtml::encode($this->pageTitle),
            ($this->pageSubtitle ? sprintf(
                ' <br /><small>%s</small>',
                (
                    $this->pageSubtitleIsHtml ?
                    $this->pageSubtitle :
                    CHtml::encode($this->pageSubtitle)
                )
            ) : '')
        );
    }

    /**
     * Overriding function to put site name at end of page title.
     */
    public function getPageTitle()
    {
        // Get the value that would have been supplied.
        $pageTitle = parent::getPageTitle();

        // If it starts with the app name (separated by a dash), move that to
        // the end.
        $appNameThenDash = Yii::app()->name . ' - ';
        if (stripos($pageTitle, $appNameThenDash) === 0) {
            $appNameThenDashLen = strlen($appNameThenDash);
            $pageTitle = substr($pageTitle, $appNameThenDashLen) .
                ' - ' . Yii::app()->name;
        }

        // Return the resulting page title.
        return $pageTitle;
    }

    protected function getPkOr404($model, $id = 'id')
    {
        return Utils::getPkOr404($_GET, $model, $id);
    }

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    /**
     * Apply access rules as defined in $this->accessRules()
     */
    public function filterAccessControl($filterChain)
    {
        $filter = new CAccessControlFilter;
        $filter->setRules($this->accessRules());
        $filter->filter($filterChain);
    }

    /**
     * Define access rules here. This is based on CAccessControlFilter,
     * See docs here: http://www.yiiframework.com/doc/api/1.1/CAccessControlFilter
     * 
     * Full example of array options:
     * 
     * array(
      'allow',  // or 'deny'

      // optional, list of action IDs (case insensitive) that this rule applies to
      // if not specified, rule applies to all actions
      'actions'=>array('edit', 'delete'),

      // optional, list of controller IDs (case insensitive) that this rule applies to
      'controllers'=>array('post', 'admin/user'),

      // optional, list of usernames (case insensitive) that this rule applies to
      // Use * to represent all users, ? guest users, and @ authenticated users
      'users'=>array('thomas', 'kevin'),

      // optional, list of roles (case sensitive!) that this rule applies to.
      'roles'=>array('admin', 'editor'),

      // since version 1.1.11 you can pass parameters for RBAC bizRules
      'roles'=>array('updateTopic'=>array('topic'=>$topic))

      // optional, list of IP address/patterns that this rule applies to
      // e.g. 127.0.0.1, 127.0.0.*
      'ips'=>array('127.0.0.1'),

      // optional, list of request types (case insensitive) that this rule applies to
      'verbs'=>array('GET', 'POST'),

      // optional, a PHP expression whose value indicates whether this rule applies
      'expression'=>'!$user->isGuest && $user->level==2',

      // optional, the customized error message to be displayed
      // This option is available since version 1.1.1.
      'message'=>'Access Denied.',

      // optional, the denied method callback name, that will be called once the
      // access is denied, instead of showing the customized error message. It can also be
      // a valid PHP callback, including class method name (array(ClassName/Object, MethodName)),
      // or anonymous function (PHP 5.3.0+). The function/method signature should be as follows:
      // function foo($user, $rule) { ... }
      // where $user is the current application user object and $rule is this access rule.
      // This option is available since version 1.1.11.
      'deniedCallback'=>'redirectToDeniedMethod',
      )
     * 
     * Role can be aliased using:
     *  *: any user, including both anonymous and authenticated users.
     *  ?: anonymous users.
     *  @: authenticated users.
     * 
     * @return array
     */
    public final function accessRules()
    {
        return array(
            array( // Admins can go anywhere.
                'allow',
                'roles' => array('admin'),
            ),
            array( // API Owners can see pages about adding/managing APIs.
                'allow',
                'controllers' => array('api'),
                'actions' => array(
                    'activeKeys',
                    'add',
                    'delete',
                    'docsEdit',
                    'edit',
                    'invitedDomains',
                    'inviteDomain',
                    'invitedUsers',
                    'inviteUser',
                    'pendingKeys',
                ),
                'roles' => array('owner'),
            ),
            array( // Developers and Owners can view basic info about APIs.
                'allow',
                'controllers' => array('api'),
                'actions' => array(
                    'addContactUs',
                    'details',
                    'index',
                    'playground',
                    'requestKey',
                ),
                'roles' => array('owner', 'user'),
            ),
            array( // Anyone can see basic info about (some) APIs (policed in the controller).
                'allow',
                'controllers' => array('api'),
                'actions' => array(
                    'details',
                    'index',
                ),
                'roles' => array('*'),
            ),
            array( // Owners can do certain things with keys.
                'allow',
                'controllers' => array('key'),
                'actions' => array('revoke'),
                'roles' => array('owner'),
            ),
            array( // Developers and Owners can see some key information.
                'allow',
                'controllers' => array('key'),
                'actions' => array(
                    'delete',
                    'details',
                    'index',
                    'mine',
                    'reset',
                ),
                'roles' => array('owner', 'user'),
            ),
            array( // Non-guests can view the Portal controller.
                'allow',
                'controllers' => array('portal'),
                'roles' => array('admin', 'owner', 'user'),
            ),
            array( // Non-guests can see the dashboard.
                'allow',
                'controllers'   => array('dashboard'),
                'actions'       => array('index', 'usageChart'),
                'roles'         => array('owner', 'user'),
            ),
            array( // Anyone can read the FAQs.
                'allow',
                'controllers' => array('faq'),
                'actions' => array('details', 'index'),
                'roles' => array('*'),
            ),
            array( // Authenticated users can view very limited info about APIs.
                'allow',
                'controllers' => array('api'),
                'actions' => array(
                    'index',
                ),
                'roles' => array('@'),
            ),
            array( // Anyone can access the public pages and auth pages.
                'allow',
                'controllers'   => array('site', 'auth'),
                'roles'         => array('*'),
            ),
            
            /* Last rule should just be deny to deny access by default unless
             * explicitly allowed above. */
            array('deny'),
        );
    }
}
