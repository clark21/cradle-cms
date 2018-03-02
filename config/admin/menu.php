<?php //-->
 return array (
  0 => 
  array (
    'icon' => 'fas fa-user',
    'label' => 'Users',
    'path' => '/admin/user/search',
  ),
  1 => 
  array (
    'icon' => 'fas fa-lock',
    'label' => 'Auth',
    'path' => '/admin/auth/search',
  ),
  2 => 
  array (
    'icon' => 'fas fa-key',
    'label' => 'Roles',
    'path' => '/admin/role',
    'children' => 
    array (
      0 => 
      array (
        'icon' => 'fas fa-search',
        'label' => 'Role Search',
        'path' => '/admin/role/search',
      ),
      1 => 
      array (
        'icon' => 'fas fa-handshake',
        'label' => 'Permissions',
        'path' => '/admin/permission/search',
      ),
      2 => 
      array (
        'icon' => 'fas fa-lock-open',
        'label' => 'Access',
        'path' => '/admin/role/auth/search',
      ),
    ),
  ),
);