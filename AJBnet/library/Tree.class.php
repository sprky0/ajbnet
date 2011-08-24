<?php

/**
 * Tree DAO
 * 
 * This will allow us to  make complex tree structures.
 *
 *	1 - NODE (ROOT node with no parent)
 *		1-1 NODE (normal node - node with a parent)
 *			1-1-1 NODE
 *				1-1-1a LEAF
 *				1-1-1b LEAF (ITEM link to any arbitrary data)
 *		1-2 NODE (normal node - node with a sibling and parent)
 *
 *
 * Node:
 * ------------
 * NodeID
 *
 * R_NodeID (parent -- NULL == "i'm root")
 * R_RootID (root -- NULL == "i'm root")
 * R_NextID (sibling next -- NULL == no prev)
 * R_PrevID (sibling prev -- NULL == no next)
 * 
 * Title
 * Note
 * 
 * Created
 * Updated
 * 
 * Leaf:
 * ----------
 * LeafID
 * R_NodeID
 * -- R_Name
 * -- R_ID
 * Created
 * Updated
 * 
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 8/2009
 *
 */
class Tree extends AJBnet_DAO {}

?>