# MadesstSecurityExtraBundle

## About problem and bundle

At first look at security.yml example from documentation:

	# app/config/security.yml
    security:
		# ...
		access_control:
			- { path: ^/admin/users, roles: ROLE_SUPER_ADMIN } # Look at path attribute. Fabien, wtf?
			- { path: ^/admin, roles: ROLE_ADMIN }

I think that default workflow with security configuration is a bit strange. I don't understand why i must support
two similar definitions of URL paths in routing.yml and security.yml. Same time i don't want to use annotations because
i prefer look at one yml without jumping from one controller to another.

MadesstSecurityExtraBundle extends security.yml so you can use your route names from routing.yml and rewrite previous example:

	# app/config/security.yml
    security:
		# ...
		access_control:
			- { path: '@my_bundle_admin_users', roles: ROLE_SUPER_ADMIN }
			- { path: '@my_bundle_admin', roles: ROLE_ADMIN }

And look routing.yml for explaining:

	# app/config/routing.yml
	my_bundle_admin:
        pattern:  /admin
        defaults: { _controller: MyBundle:Admin:index}
	my_bundle_admin_users:
		pattern:  /admin/users
		defaults: { _controller: MyBundle:Admin:users}

Old style syntax is also supported, don't worry. You can use pattern string in path, nothing will be broken.

## Installation

Add bundle into your `composer.json`:

    {
        "require": {
            "madesst/security-extra-bundle": "dev-master"
        }
    }

And register it into `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Madesst\SecurityExtraBundle\MadesstSecurityExtraBundle(),
        );
    }

Add line to parameters.yml

    // app/config/parameters.yml
    security.matcher.class: Madesst\SecurityExtraBundle\Common\RequestMatcher

That all!

## Advanced routes

MadesstSecurityExtraBundle supports simple wildcards, for that cases when you have routing.yml with a specific naming
convention, for a stupid example:

	# app/config/routing.yml
	my_bundle_post:
        pattern:  /post/{id}
        defaults: { _controller: MadesstSecurityExtraBundle:Default:read}
    my_bundle_post_edit:
        pattern:  /post/edit/{id}
        defaults: { _controller: MadesstSecurityExtraBundle:Default:update}
    my_bundle_post_create:
        pattern:  /post/create
        defaults: { _controller: MadesstSecurityExtraBundle:Default:create}
    my_bundle_post_delete:
        pattern:  /post/delete/{id}
        defaults: { _controller: MadesstSecurityExtraBundle:Default:delete}

So, you want that all users can read post, registered users can write new post and editing existing posts, and only
admins can delete posts. And all with ESI caching =) Let's write simple security.yml for this:

	# app/config/security.yml
	security:
        firewalls:
            secured_area:
                pattern:    '@*' # Equals to '^/' in old syntax
                anonymous:  ~
                form_login:
                    login_path:  '_demo_login'
                    check_path:  '_security_check'

        access_control:
            - { path: '@my_bundle_post_delete', roles: ROLE_ADMIN}
            - { path: '@my_bundle_post_*', roles: ROLE_USER}
            - { path: '@my_bundle_post', roles: IS_AUTHENTICATED_ANONYMOUSLY}
			- { path: ^/esi, roles: IS_AUTHENTICATED_ANONYMOUSLY, ip: 127.0.0.1 }
			- { path: ^/esi, roles: ROLE_NO_ACCESS }

## License

Released under the MIT License, see LICENSE.