<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014  	   HENRY Florian  florian.henry@open-concept.pro
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       dev/skeletons/referenceletterschapters.class.php
 *  \ingroup    referenceletters
 *  \brief      This file is a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class ReferenceLettersChapters extends CommonObject
{
	public $db;							//!< To store db handler
	public $error;							//!< To return error code (or message)
	public $errors=array();				//!< To return several error codes (or messages)
	public $element='referenceletterschapters';			//!< Id that identify managed objects
	public $table_element='referenceletterschapters';		//!< Name of table without prefix where object is stored

    public $id;

	public $entity;
	public $fk_referenceletters;
	public $lang;
	public $sort_order;
	public $title;
	public $content_text;
	public $options_text;
	public $status;
	public $import_key;
	public $fk_user_author;
	public $datec='';
	public $fk_user_mod;
	public $tms='';
	public $readonly='';

	public $lines_chapters = array();



    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->fk_referenceletters)) $this->fk_referenceletters=trim($this->fk_referenceletters);
		if (isset($this->sort_order)) $this->sort_order=trim($this->sort_order);
		if (isset($this->lang)) $this->lang=trim($this->lang);
		if (isset($this->title)) $this->title=trim($this->title);
		if (isset($this->content_text)) $this->content_text=trim($this->content_text);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);
		if (isset($this->readonly)) $this->readonly=trim($this->readonly);


		// Check parameters
		// Put here code to add a control on parameters values
        if (is_array($this->options_text) && count($this->options_text)>0) {
        	//Remove empty values
        	foreach($this->options_text as $key=>$option) {
				if (empty($option)) unset($this->options_text[$key]);
			}
        	$option_text=serialize($this->options_text);
        } else {
        	$option_text=trim($this->options_text);
        }

        if (empty($this->lang)) {
        	$this->lang=$langs->defaultlang;
        }

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."referenceletters_chapters(";

		$sql.= "entity,";
		$sql.= "fk_referenceletters,";
		$sql.= "lang,";
		$sql.= "sort_order,";
		$sql.= "title,";
		$sql.= "content_text,";
		$sql.= "options_text,";
		$sql.= "readonly,";
		$sql.= "status,";
		$sql.= "import_key,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod";
        $sql.= ") VALUES (";
		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->fk_referenceletters)?'NULL':"'".$this->fk_referenceletters."'").",";
		$sql.= " ".(empty($this->lang)?'':"'".$this->db->escape($this->lang)."'").",";
		$sql.= " ".(! isset($this->sort_order)?'NULL':"'".$this->sort_order."'").",";
		$sql.= " ".(! isset($this->title)?'NULL':"'".$this->db->escape($this->title)."'").",";
		$sql.= " ".(! isset($this->content_text)?'NULL':"'".$this->db->escape($this->content_text)."'").",";
		$sql.= " ".(empty($option_text)?'NULL':"'".$this->db->escape($option_text)."'").",";
		$sql.= " ".(empty($this->readonly)?'0':$this->readonly).",";
		$sql.= " ".(! isset($this->status)?'0':$this->status).",";
		$sql.= " ".(! isset($this->import_key)?'NULL':"'".$this->db->escape($this->import_key)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " '".$this->db->idate(dol_now())."',";
		$sql.= " ".$user->id;
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."referenceletters_chapters");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.entity,";
		$sql.= " t.fk_referenceletters,";
		$sql.= " t.lang,";
		$sql.= " t.sort_order,";
		$sql.= " t.title,";
		$sql.= " t.content_text,";
		$sql.= " t.options_text,";
		$sql.= " t.readonly,";
		$sql.= " t.status,";
		$sql.= " t.import_key,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms";
        $sql.= " FROM ".MAIN_DB_PREFIX."referenceletters_chapters as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->fk_referenceletters = $obj->fk_referenceletters;
				$this->lang = $obj->lang;
				$this->sort_order = $obj->sort_order;
				$this->title = $obj->title;
				$this->content_text = $obj->content_text;
				$this->options_text = unserialize($obj->options_text);
				$this->readonly = $obj->readonly;
				$this->status = $obj->status;
				$this->import_key = $obj->import_key;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);


            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

/**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_byrefltr($id,$lang_chapter='')
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.entity,";
		$sql.= " t.fk_referenceletters,";
		$sql.= " t.lang,";
		$sql.= " t.sort_order,";
		$sql.= " t.title,";
		$sql.= " t.content_text,";
		$sql.= " t.options_text,";
		$sql.= " t.readonly,";
		$sql.= " t.status,";
		$sql.= " t.import_key,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms";
        $sql.= " FROM ".MAIN_DB_PREFIX."referenceletters_chapters as t";
        $sql.= " WHERE t.fk_referenceletters = ".$this->db->escape($id);
        if (!empty($lang_chapter)) {
        	$sql.=" AND t.lang='".$this->db->escape($lang_chapter)."'";
        }
        $sql.= " ORDER BY sort_order";

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
        	$num=$this->db->num_rows($resql);
            if ($num>0)
            {
            	$this->lines_chapters=array();
                while ($obj = $this->db->fetch_object($resql)) {

	               	$chapter = new ReferenceLettersChapters($this->db);

	                $chapter->id    = $obj->rowid;

					$chapter->entity = $obj->entity;
					$chapter->fk_referenceletters = $obj->fk_referenceletters;
					$chapter->lang = $obj->lang;
					$chapter->sort_order = $obj->sort_order;
					$chapter->title = $obj->title;
					$chapter->content_text = $obj->content_text;
					$chapter->options_text = unserialize($obj->options_text);
					$chapter->readonly = $obj->readonly;
					$chapter->status = $obj->status;
					$chapter->import_key = $obj->import_key;
					$chapter->fk_user_author = $obj->fk_user_author;
					$chapter->datec = $this->db->jdate($obj->datec);
					$chapter->fk_user_mod = $obj->fk_user_mod;
					$chapter->tms = $this->db->jdate($obj->tms);

					$this->lines_chapters[]=$chapter;
                }
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->fk_referenceletters)) $this->fk_referenceletters=trim($this->fk_referenceletters);
		if (isset($this->lang)) $this->lang=trim($this->lang);
		if (isset($this->sort_order)) $this->sort_order=trim($this->sort_order);
		if (isset($this->title)) $this->title=trim($this->title);
		if (isset($this->content_text)) $this->content_text=trim($this->content_text);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->readonly)) $this->readonly=trim($this->readonly);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);


		// Check parameters
		// Put here code to add a control on parameters values
		if (is_array($this->options_text) && count($this->options_text)>0) {
			foreach($this->options_text as $key=>$option) {
				if (empty($option)) unset($this->options_text[$key]);
			}
			$option_text=serialize($this->options_text);
		} else {
			$option_text=trim($this->options_text);
		}

		if (empty($this->lang)) {
			$this->lang=$langs->defaultlang;
		}



        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."referenceletters_chapters SET";

		$sql.= " fk_referenceletters=".(isset($this->fk_referenceletters)?$this->fk_referenceletters:"null").",";
		$sql.= " lang=".(!empty($this->lang)?"'".$this->db->escape($this->lang)."'":"null").",";
		$sql.= " sort_order=".(isset($this->sort_order)?$this->sort_order:"null").",";
		$sql.= " title=".(isset($this->title)?"'".$this->db->escape($this->title)."'":"null").",";
		$sql.= " content_text=".(isset($this->content_text)?"'".$this->db->escape($this->content_text)."'":"null").",";
		$sql.= " options_text=".(!empty($option_text)?"'".$this->db->escape($option_text)."'":"null").",";
		$sql.= " readonly=".(!empty($this->readonly)?$this->readonly:"0").",";
		$sql.= " status=".(isset($this->status)?$this->status:"null").",";
		$sql.= " import_key=".(isset($this->import_key)?"'".$this->db->escape($this->import_key)."'":"null").",";
		$sql.= " fk_user_mod=".$user->id;


        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."referenceletters_chapters";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Referenceletterschapters($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->entity='';
		$this->fk_referenceletters='';
		$this->lang='';
		$this->sort_order='';
		$this->title='';
		$this->content_text='';
		$this->options_text='';
		$this->readonly='';
		$this->status='';
		$this->import_key='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';


	}

	/**
	 * Retrun max +1 sort roder for a letters model
	 *
	 * @return int	max + 1
	 */
	public function findMaxSortOrder() {
		global $langs;
		$sql = "SELECT";
		$sql.= " MAX(t.sort_order) as maxsortorder";
		$sql.= " FROM ".MAIN_DB_PREFIX."referenceletters_chapters as t";
		$sql.= " WHERE t.fk_referenceletters = ".$this->fk_referenceletters;

		dol_syslog(get_class($this)."::findMaxSortOrder sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		$max=0;
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$max = $obj->maxsortorder;


			}
			$this->db->free($resql);

			return $max+1;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::findMaxSortOrder ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Retrun max +1 sort roder for a letters model
	 *
	 * @return int	max + 1
	 */
	public function findPreviewsLanguage() {
		global $langs;
		$sql = "SELECT";
		$sql.= " t.lang";
		$sql.= " FROM ".MAIN_DB_PREFIX."referenceletters_chapters as t";
		$sql.= " WHERE t.fk_referenceletters = ".$this->fk_referenceletters;
		$sql.= " AND t.sort_order <".$this->sort_order;
		$sql.= " LIMIT 1";


		dol_syslog(get_class($this)."::".__METHOD__." sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		$max=0;
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$lang = $obj->lang;


			}
			$this->db->free($resql);

			return $lang;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::".__METHOD__." ".$this->error, LOG_ERR);
			return -1;
		}
	}


}