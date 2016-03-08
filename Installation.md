# Installing the Library #
Installing the library is as simple as extracting the zip file. Choose a directory and unarchive.

## Linux/Mac Installation ##
After downloading the zip file, simply choose a directory in which to install the library. In our example below,
we'll use `/usr/local/psYiiExtensions`. If you do not have **root** access to your web hosting environment, you can
place the library in your home directory. None of the files are required to be directly accessible from the web.

```
# cd ~
# wget http://ps-yii-extensions.googlecode.com/files/psYiiExtensions-1.0.6.zip
# cd /usr/local/
# unzip ~/psYiiExtensions-1.0.6.zip
# chown -R apache:apache psYiiExtensions
```

## Windows Installation ##
Pretty much the same as linux except we'll install this in C:\InetPub. Downloading the zip file and unzip.
In our example below, we'll use `C:\InetPub\`. If you do not have **administrator** access to your web hosting environment,
there is no reason you can't place the library in your home directory. None of the files are required to be directly
accessible from the web.

```
C:> cd \InetPub
C:> unzip drive:\download\path\psYiiExtensions-1.0.6.zip
```

# Using the Library #
In order to use the library within Yii, you must first add a new path alias to your **config/main.php** file. Place the
following line at the top of your configuration file:

## Linux/Mac ##
```
Yii::setPathOfAlias( 'pogostick', '/usr/local/psYiiExtensions/extensions/pogostick' );
```

## Windows ##
```
Yii::setPathOfAlias( 'pogostick', 'C:\inetpub\psYiiExtensions\extensions\pogostick' );
```

# Importing the Library #
You may optionally have Yii import the components of the library for you by modifying the **import** key in the
configuration array. Only the helpers, components and behaviors directories are required for use.

```
// autoloading model and component classes
'import' => array(
	'application.models.*',
	'application.components.*',
	'pogostick.base.*',
	'pogostick.behaviors.*',
	'pogostick.components.*',
	'pogostick.events.*',
	'pogostick.helpers.*',
	'pogostick.widgets.*',
),
```