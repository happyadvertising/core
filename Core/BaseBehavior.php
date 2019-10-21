<?php
/**
 * @author Basic App Dev Team
 * @license MIT
 * @link http://basic-app.com
 */
namespace BasicApp\Core;

use denis303\traits\FactoryTrait;
use BasicApp\Interfaces\BehaviorInterface;

abstract class BaseBehavior implements BehaviorInterface
{

	use FactoryTrait;

	protected $owner;

}