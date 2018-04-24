# Developer Portal #
Developer Portal website.

## Docker Image ##
This website is available as a Docker image here:  
<https://hub.docker.com/r/silintl/developer-portal/>

We recommend using that as the `FROM` in your own Dockerfile in your own
private repo, where you would `COPY` into your own Docker image the files needed
by SimpleSAMLphp (if using SAML logins), your own
`/data/public/img/logos/site-logo.png`, etc. Your Dockerfile should put the
SAML files into `/tmp/ssp-overrides`, since the `run.sh` script will copy from
there into the SimpleSAMLphp folders within the `vendor` folder after installing
composer dependencies.

### Example Dockerfile using this as the FROM ###

    FROM silintl/developer-portal:1.0.1 

    # Make sure /data is available
    RUN mkdir -p /data

    # Copy in a custom vhost configuration (if necessary)
    COPY build/vhost.conf /etc/apache2/sites-enabled/

    # Copy the SimpleSAMLphp configuration files to a temporary location
    COPY build/ssp-overrides /tmp/ssp-overrides

    COPY build/logos /data/public/img/logos

    WORKDIR /data

    EXPOSE 80

    # Record now as the build date/time (in a friendly format).
    RUN date -u +"%B %-d, %Y, %-I:%M%P (%Z)" > /data/protected/data/version.txt

    CMD ["/data/run.sh"]


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
Add the following line to your hosts file (replacing ```your-domain-name``` with
the domain name you want to use):
```192.168.33.10 your-domain-name```

## Troubleshooting ##
If you try to access <http://your-domain-name/phpmyadmin/> and get the following 
error... 

    phpMyAdmin - Error  
    Wrong permissions on configuration file, should not be world writable!

... then add the following line to the end of 
`vendor\phpmyadmin\phpmyadmin\config.inc.php`: 

    $cfg['CheckConfigurationPermissions'] = false;
