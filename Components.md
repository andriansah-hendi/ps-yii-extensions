# Why do I need a mini-framework inside a framework? #
After building a few projects using the [Yii Framework](http://www.yiiframework.com), I found I
was repeating myself in a few areas. I decided I would separate out my common base of code to make it
easier to reuse. During the separation, I started adding a few things here and there and finally ended
up with this base set of functionality.

The major features of the psYiiExtension library are as follows

  * A new base component and widget
  * New behaviors that are closely linked to these objects
  * Several components and widgets built upon this framework

I will try my best to explain how to use these objects and hopefully you will find them useful.

# Library Foundation #
The basic functionality built into the library is a dynamic variable, or options, system. Using this
funcationality, one can create options at runtime. These are stored in a way
that makes it simple to use them as arguments for your own components or third-party ones.

The second base functionality for the library is enhanced behavior functionality. Behaviors
attached to the base components can have their own options. In addition, behavior variables may be accessed
directly by the owner of the behavior with the need for a behavior prefix:

## Without psYiiExtensions... ##
```
//	Create a component and attach a behavior
$oComp = new CComponent();
$oComp->attachBehavior( 'myBehavior', '''x.y.z.behavior' );

//	Access a behavior property
$oComp->myBehavior->propertyName
```

## With psYiiExtensions... ##
```
//	Create a component, CPSComponentBehavior is automatically attached
$oComp = new CPSComponent();

//	Access CPSComponentBehavior::internalName property
echo $oComp->internalName;
```