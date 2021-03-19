<?php
namespace Sil\DevPortal\models;

use Sil\DevPortal\components\AuthManager;

class SiteText extends \SiteTextBase
{
    use \Sil\DevPortal\components\FixRelationsClassPathsTrait;
    use \Sil\DevPortal\components\FormatModelErrorsTrait;
    use \Sil\DevPortal\components\ModelFindByPkTrait;
    
    /**
     * Get the HTML to show for the specified site text. If a static file
     * exists for that site text (for example, at
     * 'protected/views/partials/home-lower-left.html'), that will be loaded.
     * Otherwise the corresponding Markdown (if any) will be retrieved from the
     * database, converted to HTML, and returned.
     *
     * @param $name
     * @return bool|null|string
     */
    public static function getHtml($name)
    {
        if (self::staticFileExists($name)) {
            return self::getContentsOfStaticFile($name);
        }
        
        $siteText = self::model()->findByAttributes(array(
            'name' => $name,
        ));
        
        if ($siteText === null) {
            return null;
        }
        
        $markdownParser = new \CMarkdownParser();
        return $markdownParser->safeTransform($siteText->markdown_content);
    }
    
    protected static function getContentsOfStaticFile($siteTextName)
    {
        $pathToStaticFile = self::getPathToStaticFile($siteTextName);
        return file_get_contents($pathToStaticFile);
    }
    
    protected static function getPathToStaticFile($siteTextName)
    {
        return __DIR__ . '/../views/partials/' . $siteTextName . '.html';
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SiteText the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                'name',
                'unsafe',
                'on' => 'update',
            ),
            array(
                'name',
                'filter',
                'filter' => array($this, 'slugify'),
            ),
            array(
                'name',
                'match',
                'pattern' => '/^[a-z-]+$/',
                'message' => 'Please use only lowercase letters (a-z) and hyphens (-) in the name.',
            ),
            array(
                'name',
                'unique',
                'allowEmpty' => false,
                'caseSensitive' => false,
            ),
        ), parent::rules());
    }
    
    public function slugify($name)
    {
        return AuthManager::slugify($name);
    }
    
    public static function staticFileExists($siteTextName)
    {
        $pathToStaticFile = self::getPathToStaticFile($siteTextName);
        return file_exists($pathToStaticFile);
    }
}
