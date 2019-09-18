# How to contribute

JTL-Shop is a commercial open source software. Read [LICENSE.md](LICENSE.md) for further information. 

Git Repository: git@gitlab.jtl-software.de:jtlshop/shop4.git

Contribute your changes by adding a new branch and creating a merge request in gitlab. 
External developers: fork shop project master in your namespace and create the merge request.  

Merging into master branch is only permitted to developers with master permission. 

## Getting Started

* Make sure your ssh key is stored in your gitlab account
* Clone the jtl-shop repository: ```git clone git@gitlab.jtl-software.de:jtlshop/shop4.git mydevshop```
* init + update submodules: 
  ```
  git submodule init
  git submodule update
  ```
* get vendor libs: 
  ```
  cd includes
  composer update
  ```
* install shop in your browser /install/index.php or get shopcli to perform install/update/migrations: https://gitlab.jtl-software.de/jtlshop/shopcli

## Coding Guidelines

We basically follow [PSR-2](http://www.php-fig.org/psr/psr-2/) with some extra rules, specified in /.php-cs. 

Grab and install php-cs-fixer to fix php-style in jtl-shop automatically: 

````
wget http://get.sensiolabs.org/php-cs-fixer.phar -O php-cs-fixer
sudo chmod a+x php-cs-fixer
sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer
```

Fix all php Files but not the exluded ones: 
```
php-cs-fixer fix .
```

Fix 1 File: 
```
php-cs-fixer fix admin/includes/dashboard_inc.php --config-file .php_cs
```



## How to write good issues

Always explain the conditions which led you to write this issue. 
Any reader must be able to understand your idea or reproduce a bug without having to ask for examples, use cases or sample-data.  

German language is preferred for issues + changelog because JTL primary addresses the german-speaking market

* Keep your issue titles short and descriptive
* Format your description with [Gitlab flavoured markdown](https://gitlab.jtl-software.de/help/markdown/markdown): Make use of syntax highlighting, lists, links and images



## Commit Messages

Always provide a short summary of your Codechange in the first line. 
Long description is optional. If needed, place a new line between summary and long description.  

Summary (first line): 

Provide a short description about the change and use words like "Improve, Fix, Add, Remove, Shorten, Update" e.g. to keep a good readability.


Issue References: 

End your commit message with "Fix" or "Re" or "Unfix" followed by the issue referenced. 
If your commit message is 1 line only, feel free to place your issue reference at the end of that line. 
Otherwise place a new line above the reference.  

Good: 
```
Fix wrong comparison operator. Fix #1234 and #1236
```
```
Add required attribute to mandatory fields. 

Re #1234
Re #1236"
```
```
Roll back last changes because jtlshop/shop4#12346 already solves this issue. 

Unfix jtlshop/shop4#12345"
```

Bad: 
```
git commit -m ""
```
```
git commit -m "sql"
```
```
git commit -m "Schönheitskorrektur"
```
```
git commit -m "wrong comparison operator used"
```

Also check out this [article about writing good commit messages](http://chris.beams.io/posts/git-commit/)