<?php
/**
 * @package     Joomla.Plugin
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Image\ImageFilterRegistry as defaultIFR;
use Joomla\CMS\Image\Filter\Brightness;
use Joomla\CMS\Factory;
use Joomla\CMS\Customimage\Filter\SuperBrightness;
use Joomla\CMS\Customimage\CustomImage;
use Joomla\CMS\Customimage\CustomImageFilterRegistry as customIFR;
use Joomla\CMS\Customimage\ImageFilterRegistry;

/**
 * Joomla! Language Filter Plugin.
 *
 * @since  1.6
 */
class PlgSystemImageFilterTest extends CMSPlugin
{


	/**
	 * The service registry for Image Filters
	 *
	 * @var    ImageFilterRegistry
	 * @since  __DEPLOY_VERSION__
	 */


	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}
	/**
	 * After initialise.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onAfterInitialise()
	{	
		Log::add('Image Registry Test initialized', Log::DEBUG, 'IFR');
		// $this->checkDefaultImageClassWithCustomFilter("superbrightness");
		// $this->checkCustomImageClass("superbrightness");

		$this->checkCustomFilterRegistry();
	}
	
	public  function checkDefaultImageClassWithCustomFilter($type)
	{
		$image = new Image(imagecreatetruecolor(1, 1));
		Log::add('Default created', Log::DEBUG, 'IFR');

		// Verify that the filter type exists.
		$serviceRegistry = Image::getServiceRegistry();
		if(!$serviceRegistry->hasService($type)){
			$serviceRegistry->register($type, SuperBrightness::class);
		}
		$className = $this->getClassName($type, $serviceRegistry, Image::class);
		// Instantiate the filter object.
		$instance = new $className($image->getHandle());

		if(!$this->isValid($instance, SuperBrightness::class)){
			throw new \RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');
		}

	}
	public  function checkCustomImageClass($type)
	{
		$image = new CustomImage(imagecreatetruecolor(1, 1));
		// Verify that the filter type exists.
		Log::add('CustomImage created', Log::DEBUG, 'IFR');

		$serviceRegistry =  CustomImage::getServiceRegistry();
		if(!$serviceRegistry->hasService($type)){
			$serviceRegistry->register($type, SuperBrightness::class);
			Log::add('CustomFilter ' . SuperBrightness::class . ' registered', Log::DEBUG, 'IFR');
		}
		$className = $this->getClassName($type, $serviceRegistry, CustomImage::class);

		// Instantiate the filter object.
		$instance = new $className($image->getHandle());

		if(!$this->isValid($instance, SuperBrightness::class)){
			throw new \RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');
		}
	}

	/**
	 *	get the classname by type
	 *
	 */
	public function getClassName($type, $serviceRegistry, $imageclass)
	{
		if ($serviceRegistry->hasService($type))
		{
			$className =  $imageclass::getServiceRegistry()->getService($type);
		}
		else{
			throw new \RuntimeException('The ' . $serviceRegistry . ' has no service of type ' . $type);
		}
		if(!$className){
			throw new \RuntimeException('The ' . ucfirst($className) . ' className is null.');
		}
		return $className;
	}

	/**
	 *	get the classname by type
	 *
	 */
	public function checkCustomFilterRegistry()
	{
		// 1) überschreibe ImageFilterRegistry mit Factory::getContainer  static::$serviceRegistry = Factory::getContainer()->set(ImageFilterRegistry::class, new customRegistry);
		// 2) instantiere image
		// 3) image->filter(Brightness) hier ist der custom brightness
		Factory::getContainer()->set(\Joomla\CMS\Image\ImageFilterRegistry::class,new \Joomla\CMS\Customimage\ImageFilterRegistry);
		$options[IMG_FILTER_BRIGHTNESS] = 50;
		$image = new Image(imagecreatetruecolor(1, 1));
		$image->filter("brightness",$options);

	}


	/**
	 *	checks if a given instance is valid
	 *
	 */
	public function isValid($instance, $instanceCheckType)
	{
		// Verify that the filter type is valid.
		if (!($instance instanceof $instanceCheckType))
		{
			throw new \RuntimeException('The ' . get_class($instance) . ' image filter with is not valid.');
		}
		Log::add('Verified that the ' . get_class($instance) . ' filter is of type ' . $instanceCheckType . ' and therefore valid', Log::DEBUG, 'IFR');
		return TRUE;
	}
}
