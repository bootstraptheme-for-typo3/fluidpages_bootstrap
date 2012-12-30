TYPO3 extension Fluid Pages - Bootstrap: Fluid Page Templates for Twitter Bootstrap
===================================================================================

## What is it?

An integration provider enabling page templating with Fluid templates, using Flux for highly dynamic page variable configuration.

## What does it do?

Provides a set of template files and static TypoScript necessary to include and use those templates as page templates.

## How does it do it?

By leveraging the integration logic provided by `EXT:fluidpages` - enabling use of specially constructed Fluid templates as
page templates, much like TemplaVoila page templates.

## How is it installed?

Download, install the extension and include the static TypoScript configuration.

## How is it used?

After installation and inclusion of the static TypoScript configuration, a new group of templates is added to the page template
selection boxes in page properties - configurable with a page template to use for all subpages, just like TemplaVoila.

## References

* https://github.com/NamelessCoder/flux is a dependency and is used to configure how the content template variable are defined.
* https://github.com/NamelessCoder/vhs is a highly suggested companion extension for creating Fluid Page templates
* https://github.com/NamelessCoder/fluidpages_bootstrap is a collection of Fluid Page templates written for Twitter Bootstrap using
  VHS ViewHelpers