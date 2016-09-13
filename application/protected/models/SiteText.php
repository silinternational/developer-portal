<?php

class SiteText extends SiteTextBase
{
    use Sil\DevPortal\components\FormatModelErrorsTrait;
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    public function afterSave()
    {
        parent::afterSave();
        
        $this->saveHtmlToFile();
        
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
    
    protected function saveHtmlToFile()
    {
        $reSanitizedName = $this->slugify($this->name);
        if ($reSanitizedName !== $this->name) {
            throw new \Exception('Invalid site text name: ' . $this->name, 1473778429);
        }
        
        $markdownParser = new \CMarkdownParser();
        $renderedHtml = $markdownParser->safeTransform($this->markdown_content);
        
        $filePath = sprintf(
            '%s/../views/partials/site-text/%s.php',
            __DIR__,
            $reSanitizedName
        );
        $result = file_put_contents($filePath, $renderedHtml);
        if ($result === false) {
            throw new \Exception(
                'Failed to save the HTML generated from this Site Text\'s '
                . 'markdown content to the appropriate view file.',
                1473778699
            );
        }
    }
    
    public function slugify($name)
    {
        return (string)\Stringy\StaticStringy::slugify($name);
    }
}
