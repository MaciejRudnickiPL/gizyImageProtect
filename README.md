# GizyImageProtect v1.0
> The class for create protected image without use JavaScript. Use only php and css 3.0


## Table of contents
* [General info](#general-info)
* [Technologies](#technologies)
* [Setup](#setup)
* [Features](#features)
* [Status](#status)
* [Inspiration](#inspiration)
* [Contact](#contact)

## General info
The class for create protected image without use JavaScript. Use only php and css 3.0.  
This class generates two png images, each with transparent areas. Images, after overlapping, complement each other, creating a full picture.

You can: 
* creating a back image (png)
* creating a front image (png)
* resize image 
* get html with css styles 

## Technologies
* PHP 5.0 =<
* CSS
* Html

## Setup
dowload class
set PHP GD library 

## Code Examples
```* GIP - Gizy Image Protection
confiugure:
class GipConfig:
public $config = [
        'pettern' => [ // pettern of png mask
            'width' => 30,// width of pettern
            'height' => 30,// height of pettern
            'marginHeight' => 15, // margin height
            'marginWidth' => 15// margin Width
        ],
        'dirImage' => 'srcImg',// directory for output png image
    ];

 <?php
    include 'Gip.php';
    $gip = new \gizyGip\Gip('image.jpg');   //image to protect
    $gip->createProtectImg(); //no resize
    echo $gip->getHtmlImgProtect();//get html 

    $gipResize = new \gizyGip\Gip('image1.jpg');  //image to protect
    $gipResize->createProtectImgResize(300, 500); //resize to 300x500px
    echo $gipResize->getHtmlImgProtect();//get html 

    $gipResize2 = new \gizyGip\Gip('image2.jpg');  //image to protect
    $gipResize2->createProtectImgResize(300, null); //resize to 300px witdh and scaled height
    echo $gipResize2->getHtmlImgProtect();//get html 


    $gipResize3 = new \gizyGip\Gip('image3.jpg');  //image to protect
    $gipResize3->createProtectImgResize(null, 300); //resize to 300px height and scaled width
    echo $gipResize3->getHtmlImgProtect(); //get html 

    ?>
```

## Features
__List of features ready and TODOs for future development__

__Features:__
* creating a back image (png)
* creating a front image (png)
* resize image 
* get html with css styles 

__To-do list:__
* add adds to config 
* start testing
* add array for list of images

## Status
v 1.0
testing 

## Contact
Created by [maciejrudnickipl@gmail.com] - feel free to contact me!



