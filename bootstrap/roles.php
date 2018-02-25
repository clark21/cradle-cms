<?php //-->
return function () {
    // add global methods
    $this->package('global')

    /**
     * Check if the role exists on the
     * given request session.
     *
     * @param *string $role
     * @param Request $request
     */
    ->addMethod('role', function($role, $request) {
        // get session
        $session = $request->getSession('me');

        // if session is empty ignore
        if(empty($session) || !isset($session)) {
            return;
        }

        if(!isset($session['role_permissions'])
            && !empty($session['role_permissions'])) {
            return true;
        }

        // get permissions
        $permissions = $session['role_permissions'];

        // if role is set
        if(in_array($role, $permissions)) {
            return true;
        }

        return false;
    });
};
