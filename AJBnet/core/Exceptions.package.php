<?php

/**
 * AJBnetExceptions, base AJBnet exceptions class.
 * 
 * This class provides exactly the same functionality
 * as php's Native Exception class, but lets us reserve the
 * right to add more functionality in the future without breaking
 * out other custom exceptions.
 * 
 * @package AJBnet
 * @subpackage Exceptions
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class AJBnetException extends Exception {}


/**
 * Database exceptions class.
 * 
 * DatabaseExceptions are thown if Queries fail
 * 
 */
class DatabaseException extends AJBnetException {}


/**
 * Application exceptions class.
 * 
 * ApplicationExceptions are thrown if the
 * system is asked to do something impossible
 * or if other runtime exceptions are encountered
 * 
 */
class ApplicationException extends AJBnetException {}


/**
 * Exceptions to be used for security violations or authentication problems.
 * 
 * SecurityExceptions are thrown if the action is not available to the current User
 * or if the User is unlogged, but is expected to be authenticated.
 * 
 */
class SecurityException extends AJBnetException {}


?>