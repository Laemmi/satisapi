# laemmi/satisapi
Simple api to write satis.json form e.g. Gitlab webhook

## Description
You can use this simple app to write new repositories inside the satis.json. 

## Installation

via composer

    composer create-project --prefer-dist laemmi/satisapi satisapi

or use repository

    git clone https://github.com/Laemmi/satisapi.git

Copy config.php.dist

    cp config/config.php.dist config/config.php

Set path to satis.json file and enter Gitlab API Token 

For development you can use

    composer serve

## Use

Call api e.g. via webhook from gitlab. These headers are required

    Content-Type: application/json
    X-Gitlab-Token: MySecretKey
    
As body you send an json string like this

    {
        "project": {
            "git_ssh_url": "git@github.com/Laemmi/satisapi.git"
        }
    }
