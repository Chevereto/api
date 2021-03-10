# Permissions

This is a document for keeping track on the permissions system in Chevereto API. This aims to implement RBAC for user authorization on top of Casbin.

## Roles

- Guest
- Registered

## Business Settings

- content/privacy/mode
- crypt/id_padding
- crypt/salt
- email/mode
- email/smtp/server/password
- email/smtp/server/port
- email/smtp/server/security
- email/smtp/server/username
- language
- routing/album
- routing/image
- routing/user
- timezone
- user/age/min
- website/mode

## Business Rules

- akismet (*)
- follower (*)
- like (*)
- maintenance
- moderatecontent (*)
- stop forum spam (*)
- language/selectable
- language/selectable/{es, fr, cn, etc}
- albums/explore/count_min

## User Permissions

The RESTful based model enables to define access to endpoints, it shares the definition with routing and is easier to follow.

- account/GET
- _account/assets_
  - account/assets/avatar/POST
  - account/assets/avatar/DELETE
  - account/assets/background/POST
  - account/assets/background/DELETE

- image/PATCH
- image/GET
- image/DELETE
- _image/upload_
  - image/upload/base64/POST
  - image/upload/binary/POST
  - image/upload/url/POST
- _images/explore_
  - images/explore/popular/GET
  - images/explore/recent/GET
  - images/explore/trending/GET
- images/search/GET
- images/random/GET
  
Anything ending in `/POST|GET|DELETE...` are permissions for accessing each given endpoint. Everything else is just a permission, intended to be used in any context:

- _image/upload_
  - image/upload/jpg
  - image/upload/gif
  - image/upload/png
  - image/upload/webp
  - image/upload/bmp
  - image/upload/duplicate
  - image/upload/checking
  - image/upload/expirable

The permissions above will determine access to upload `webp` images, or if `duplicate` upload is allowed.

## Attributes

Attributes complement the permission system and provides context for the role permissions. I don't know how I will implement this, but is all about the limits per actor defined by roles.

- account/asset/avatar/filesize_max
- account/asset/background/filesize_max

- flood/uploads/day
- flood/uploads/hour
- flood/uploads/minute
- flood/uploads/month
- flood/uploads/week

- list/items_per_page

- image/upload/default/expiration
- image/upload/filesize/max
- image/upload/height/max
- image/upload/width/max
- image/upload/moderation/required

## Settings

Settings are per-user attributes, the good old relational database.

- image/upload/filenaming
- image/upload/exif/keep
- image/upload/storage/mode

## Deprecations

- nsfw lock editing
- nsfw show in listings
- nsfw show blurred
- nsfw show banners
- nsfw enable random

NSFW goes on a dedicated section now.
