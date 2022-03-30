# symfony-sample-app

Here lies the source code for the test task, the very full copy of the test task is not placed here due to security reasons.

By now the running application can be accessed on http://95.211.120.226:2020/

The application contains two authenticators: ApiTokenAuthenticator.php and  AppCustomAuthenticator.php


By rules declared in 

### routes.yaml:

The first protects the API endpoint /api/users
The second stands for user session authentication.


```
       api:
            pattern: ^/api
            stateless: true 
            custom_authenticator:
                - App\Security\ApiTokenAuthenticator
        main:
            lazy: true
            provider: app_user_provider
            custom_authenticator:
                - App\Security\AppCustomAuthenticator
```

Initially, after passing custom user authentication, application triggers user-defined UserAuthenticatedEvent, which is then sent for processing to the UserSubscriber event subscrber where the api token is assigned to the user.
After that the user is routed to the home route: "/" run by MainController.

Then, in Controller application makes an HttpClient request ( wrapped as a service) with an issued api token.

>> The application should be resilient to large amounts of traffic at very specific hours of the day. This should be taken into account when writing the configuration files.

Due to that reason users list are stored in initially available (no need to add anything to config here) FileSystem cache. (Symfony's cache is resilient to cache stampede since v.4)
The cache is updated once in 5 seconds on demand.

Also, assuming the application is run behind a load-balancer, trusted proxies are added as network submasks for any private network address are added to 

### packages/framework.yaml 

```
framework:
    # any local addresses okay for proxying from load balancer
    trusted_proxies: '192.168.0.0/16,172.16.0.0/16,10.0.0.0/8'
    trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']
    trusted_headers: ['forwarded']
```
 



