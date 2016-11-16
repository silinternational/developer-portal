<?php
namespace Sil\DevPortal\components;

/**
 * Trait to add a wrapper around Yii's CActiveRecord's findByPk to force it to
 * return null if a null pk was provided. MySQL has a bug/feature where it will
 * return the last inserted record if a null pk is requested. See
 * http://stackoverflow.com/a/32396258/3813891 or
 * http://www.yiiframework.com/forum/index.php/topic/23870-findbypknull-returns-an-object/page__p__145348#entry145348
 *
 * @author Matt Henderson
 */
trait ModelFindByPkTrait
{
	public function findByPk($pk, $condition = '', $params = array())
    {
        if ($pk === null) {
            return null;
        }
        return parent::findByPk($pk, $condition, $params);
    }
}
