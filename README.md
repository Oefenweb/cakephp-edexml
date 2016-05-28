# Edexml plugin for CakePHP

[![Build Status](https://travis-ci.org/Oefenweb/cakephp-edexml.png?branch=master)](https://travis-ci.org/Oefenweb/cakephp-edexml) [![PHP 7 ready](http://php7ready.timesplinter.ch/Oefenweb/cakephp-edexml/badge.svg)](https://travis-ci.org/Oefenweb/cakephp-edexml) [![Coverage Status](https://coveralls.io/repos/Oefenweb/cakephp-edexml/badge.png)](https://coveralls.io/r/Oefenweb/cakephp-edexml) [![Packagist downloads](http://img.shields.io/packagist/dt/Oefenweb/cakephp-edexml.svg)](https://packagist.org/packages/oefenweb/cakephp-edexml) [![Code Climate](https://codeclimate.com/github/Oefenweb/cakephp-edexml/badges/gpa.svg)](https://codeclimate.com/github/Oefenweb/cakephp-edexml)

## Requirements

* CakePHP 2.4.2 or greater.
* PHP 5.4.16 or greater.

## Installation

Clone/Copy the files in this directory into `app/Plugin/Edexml`

## Configuration

Ensure the plugin is loaded in `app/Config/bootstrap.php` by calling:

```
CakePlugin::load('Edexml');
```

## Usage

### Validating Edexml file upload from a controller

First include the plugin model in your controller:

```
public $uses = array('Edexml.Edexml');
```

Validate Edexml file:

```
$this->Edexml->saveAll($this->request->data, array('validate' => 'only'))
```

And in the corresponding view:

```
echo $this->Form->create('Edexml.Edexml', array('type' => 'file'));
echo $this->Form->input('file', array('type' => 'file'));
echo $this->Form->end(__('Submit'));
```

### Parse Edexml file to a normalized array

First include the plugin model in your controller:

```
public $uses = array('Edexml.Edexml');
```

Convert the Edexml file:

```
$filename = $this->request->data('Edexml.file.tmp_name');
$data = $this->Edexml->parseToArray($filename);
$data = $this->Edexml->convert($data);
```
