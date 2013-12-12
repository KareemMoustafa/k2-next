<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/resource.php';
require_once JPATH_SITE.'/components/com_k2/helpers/route.php';

/**
 * K2 user resource class.
 */

class K2Users extends K2Resource
{

	/**
	 * @var array	Items instances container.
	 */
	protected static $instances = array();

	/**
	 * Constructor.
	 *
	 * @param object $data
	 *
	 * @return void
	 */

	public function __construct($data)
	{
		parent::__construct($data);
		self::$instances[$this->id] = $this;
	}

	/**
	 * Gets an item instance.
	 *
	 * @param integer $id	The id of the item to get.
	 *
	 * @return K2Tag The tag object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Users');
			$model->setState('id', $id);
			$item = $model->getRow();
			self::$instances[$id] = $item;
		}
		return self::$instances[$id];
	}

	/**
	 * Prepares the row for output
	 *
	 * @param string $mode	The mode for preparing data. 'site' for fron-end data, 'admin' for administrator operations.
	 *
	 * @return void
	 */
	public function prepare($mode = null)
	{
		// Prepare generic properties like dates and authors
		parent::prepare($mode);

		// Prepare specific properties
		$this->editLink = '#users/edit/'.$this->id;

		// Link
		$this->link = $this->getLink();

		if ($this->id)
		{
			$this->enabled = (int)!$this->block;
			$this->activated = (int)!$this->activation;
			if (isset($this->groups))
			{
				$this->groupsValue = implode(', ', $this->groups);
			}
		}

		// Image
		$this->image = $this->getImage();

	}

	public function getLink()
	{
		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->name);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->name);
		}
		return JRoute::_(K2HelperRoute::getUserRoute($this->id.':'.$this->alias));
	}

	public function getImage()
	{
		$image = null;
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
		$result = K2HelperImages::getResourceImages('user', $this);
		$this->_image = new stdClass;
		if ($result->image)
		{
			$image = $result->image;
			$this->_image->preview = $result->image;
			$this->_image->id = $result->id;
		}
		return $image;
	}

	public function getEvents()
	{
		$events = new stdClass;
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$results = $dispatcher->trigger('onK2UserDisplay', array(
			&$this,
			&$params,
			0
		));
		$events->K2UserDisplay = trim(implode("\n", $results));
		return $events;
	}

}
