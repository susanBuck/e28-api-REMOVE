# e28-api

```bash
$ chmod +x ./setup.sh
```

```bash
$ ./setup.sh
```


## About
This is a pre-built application created for use as a practice API in [DGMD E-28 Single-page Applications & Interfaces with Vue.js](https://hesweb.dev/e28).

+ Built with Laravel
+ Uses a SQLite database
+ Intended only for practice/demo purposes



## Setup - Local
To set up locally, clone this project within your *htdocs/e28* directory.

```
$ git clone git@github.com:susanBuck/e28-api.git
```

Set up a local domain, *http://e28-api.loc*, that has a document root pointing to `/path/to/htdocs/e28-api/core/public`.

Visit <http://e28-api.loc/config> to begin.



## Configure resources
A resource is a data entity you’ll track within your application. For example, in ZipFoods, we’d have resources like *product* and/or *order*.

To configure resources for your API, duplicate and update the example `/resources/product-resource.json`.

Note: Resource names must be **singular** and **one word**

Valid resource name examples:
+ `product`
+ `book`

Invalid resource name examples:
+ `products`
+ `Products`
+ `new_product`

Note how each resource has a series of fields for which you set the *type* and any *validators*.

Type options include:
+ `string`
+ `text`
+ `integer`
+ `float`
+ `boolean`
+ `timestamp`

Validator options include:
+ `required` The field under validation must be present in the input data and not empty.
+ `min:value` The field under validation must have a minimum value. 
+ `max:value` The field under validation must be less than or equal to a maximum value. 
+ `alpha` The field under validation must be entirely alphabetic characters.
+ `alpha_num` The field under validation must be entirely alpha-numeric characters.
+ `numeric` The field under validation must be numeric.
+ `email` The field under validation must be formatted as an e-mail address.
+ `boolean` The field under validation must be able to be cast as a boolean. 

Any data passed to the API for a given field that does not match the given type or meet the validator requirements will be rejected with an error.




## Seeds
To configure seeds for your API, duplicate and update the example `/seeds/product-seeds.json`.

TODO: Naming conventions




## Setup - Production
Synchronize your local and production e28 repository so that the e28-api application is available on production at `/var/www/html/e28/e28-api`

Configure a production domain, http://e28-api.yourdomain.com so that its document root is `/var/www/html/e28-api/core/public`.

