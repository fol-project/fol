<?php
/**
 * Fol\Utils\Acl
 * 
 * Provides a basic ACL (access control list)
 * Example:
 * 
 * class User {
 * 	use Fol\Utils\Acl;
 * }
 * 
 * $User = new User();
 * 
 * $User->setPermission('write', function ($User, $Post) {
 * 	if ($Post->author === $User->id) {
 * 		return true;
 * 	}
 * 
 * 	return false;
 * });
 * 
 * $User->setPermission('fly', false);
 * 
 * if ($User->can('write', $Post)) {
 * 	echo 'You can write in this post!!';
 * }
 * 
 * if (!$User->can('fly')) {
 * 	echo 'You cannot fly!!';
 * }
 */
namespace Fol\Utils;

trait Acl {
	private $permissions = array();

	/**
	 * Define a list of permissions for the user
	 * 
	 * @param array $permissions The permissions to define. The keys are the permission name and the values the resolver or boolean value.
	 */
	public function setPermissions (array $permissions) {
		$this->permissions = array_replace($this->permissions, $permissions);
	}


	/**
	 * Define a new permission
	 * 
	 * @param string $permission The permission name
	 * @param Closure/Boolean $resolver The function to resolver this permission or a boolean value to define the permission directly. The first argument will be the current class following by other optional arguments passed 
	 */
	public function setPermission ($permission, $resolver) {
		if (is_array($permission)) {
			foreach ($permission as $permission) {
				$this->permissions[$permission] = $resolver;
			}
		} else {
			$this->permissions[$permission] = $resolver;
		}
	}


	/**
	 * Check if the user has a specific permission
	 * 
	 * @param string $permission The permission name to check
	 * 
	 * @return boolean True if the user has permission, false if not
	 */
	public function can ($permission) {
		if (!isset($this->permissions[$permission])) {
			return false;
		}

		if (is_callable($this->permissions[$permission])) {
			$arguments = array_slice(func_get_args(), 1);
			array_unshift($arguments, $this);

			return call_user_func_array($this->permissions[$permission], $arguments);
		}

		return (bool)$this->permissions[$permission];
	}
}
?>