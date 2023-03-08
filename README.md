# Developer Portal #
Developer Portal website.

## Docker Image ##
This website is available as a Docker image here:  
<https://hub.docker.com/r/silintl/developer-portal/>

We recommend using that as the `FROM` in your own Dockerfile in your own
private repo, where you would `COPY` into your own Docker image the files needed
by SimpleSAMLphp (if using SAML logins), your own
`/data/public/img/logos/site-logo.png`, etc.

Your Dockerfile should (in this order)...

1. Put any custom SAML files into `/tmp/ssp-overrides`.
2. Run the `/tmp/install-deps-and-ssp-overrides.sh`, since it will move the SAML
   files into the SimpleSAMLphp folders within the `vendor` folder after
   installing composer dependencies.

### Example Dockerfile using this as the FROM ###

    # Change 4.1.6 to the latest tagged version or whichever you want to have
    FROM silintl/developer-portal:4.1.6
    
    ENV REFRESHED_AT 2021-04-08
    
    # Put in place any additional custom SAML files:
    COPY build/ssp-overrides /tmp/ssp-overrides
    
    # Put dependencies and SSP overrides in their final location
    RUN /tmp/install-deps-and-ssp-overrides.sh
    
    # Copy in any custom files needed, which are stored in this repo.
    COPY build/favicons /data/public
    COPY build/logos /data/public/img/logos
    
    WORKDIR /data
    
    EXPOSE 80
    
    # Record now as the build date/time (in a friendly format).
    RUN date -u +"%B %-d, %Y, %-I:%M%P (%Z)" > /data/protected/data/version.txt
    
    CMD ["/data/run.sh"]


## A Note About Semantic Versioning ##
The environment variables that this code uses are (for the purposes of
semantic versioning) considered this code's public interface. That is how
backwards-compatibility will be determined. If a new version of this code is
released that bumps the major version number (e.g. from `1.x.y` to `2.0.0`),
you will probably have to change something about what environment variables
you are providing when running this Docker image.

## Environment / Project Setup ##
1. Install [VirtualBox](http://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](http://downloads.vagrantup.com/)
3. Clone the repository:  
   ```git clone git@github.com:silinternational/developer-portal.git```
4. Copy the ```local.env.dist``` file to ```local.env```, and update its 
   contents appropriately.
5. Open a shell/terminal in the project's root folder.
6. Launch environment with ```vagrant up```
7. Modify your hosts file per the instructions below.
8. Open browser and go to <http://your-domain-name/>
9. If you need to make yourself an admin, login first (to make sure your user
   record exists), then go to    <http://your-domain-name/phpmyadmin>.
   Login with the project's database credentials, find your user in the user
   table, and change the role to 'admin'.

### local.env reference ###
- ```SHOW_POPULAR_APIS``` (boolean)  
  Whether to show the most popular APIs (based on the number of approved keys).
  If false, the file ```application/protected/views/partials/home-lower-right.php```
  will be shown (which you can set the content of in the `site_text` table in
  the database).

## Hosts file modification for running Developer Portal locally ##
Add one of the following lines to your hosts file (replacing
```your-domain-name``` with the domain name you want to use).

If using Vagrant:  
```192.168.33.10 your-domain-name```

If using Docker directly (without Vagrant):  
```127.0.0.1 your-domain-name```

### Local API for development/testing

If you want to add an API to the developer portal locally, such as for
development or manual testing...

1. Bring up the developer portal: `make`
2. Bring up phpMyAdmin: `make phpmyadmin`
3. Bring up httpbin: `make httpbin`
4. Log in to the developer portal (e.g. <http://localhost/> or your custom domain name)
5. Log in to phpMyAdmin:
   - Go to <http://localhost:8001>
   - Use `developer_portal` as the username and password
   - Change your user record's `role` to "admin"
6. In the developer portal, add an API:
   - When logged into the local developer portal, click "Browse APIs"
   - Click the "Publish a new API" button
   - Use `test` as the API code name, `httpbin` as the Endpoint, "HTTP" as the
     Endpoint Protocol, and whatever you want for the other values
   - Click the "Save" button
7. Grant yourself a key to that API ("Actions" > "Get/Request Key", etc.)
8. Click the "API Playground" link in the header menu
9. Put `/anything` in the Path field (literally) and submit the form
10. You should see an API response (at the bottom) from the local httpbin docker
    container.

## Troubleshooting ##
If you try to access <http://your-domain-name/phpmyadmin/> and get the following 
error... 

    phpMyAdmin - Error  
    Wrong permissions on configuration file, should not be world writable!

... then add the following line to the end of 
`vendor\phpmyadmin\phpmyadmin\config.inc.php`: 

    $cfg['CheckConfigurationPermissions'] = false;
