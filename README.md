phptags
=======
v0.1 by Evan Coury

About phptags
-------------

This utility generates more intelligent PHP tag files for vim. This project
leverages the token scanners in Zend\Code from Zend Framework 2.

Goals:
------

* Fast
* Use PHP's tokenizer to get more context information than exuberant-ctags regex
  parsing
* Namespace-aware
* Append parameter/argument information for methods/functions
* Eventually support traits
