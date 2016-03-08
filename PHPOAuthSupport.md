# Introduction #

With the release of v1.0.3, the psYiiExtensions library supports the [PHP oauth library](http://pecl.php.net/package/oauth). In order to utilize this you must first install the library and enable it.

This mini-guide will explain how to do it.

# Details #

I'm not certain about Windows, but I'm sure it's similar.

In a nutshell, here's what you need to do:

1. Obtain the oauth library from the above repository. If you do not have root access to your machine, speak with your network administrator and/or hosting provider to see if they can install the library for you.

I installed it with pecl like this:
```
# pecl install oauth
```


2. Add the following line to your php.ini file:
```
extensions=oauth.so
```

3. Restart your web server

# Testing #

If you try and create a CPSTwitterApi object, if oauth is not installed properly it will throw an exception.

If you need help, let me know.