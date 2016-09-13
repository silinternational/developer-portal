<?php

class SiteText extends SiteTextBase
{
    use Sil\DevPortal\components\FormatModelErrorsTrait;
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    public function afterSave()
    {
        parent::afterSave();
        
        /** @todo Generate the HTML from this Markdown content and save it to the applicable partial view file. */
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
                'filter',
                'filter' => array($this, 'slugify'),
            ),
            array(
                'name',
                'match',
                'pattern' => '/^[a-z-]+$/',
                'message' => 'Please use only lowercase letters (a-z) and hyphens (-) in the name.',
            ),
        ), parent::rules());
    }
    
    public function slugify($name)
    {
        return (string)\Stringy\StaticStringy::slugify($name);
    }
}
