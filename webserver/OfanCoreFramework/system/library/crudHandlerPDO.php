<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class crudHandler
 *
 * Adalah kelas untuk mengatasi permintaan database dari user pada server dalam bentuk universal
 * Didefinisikan sesuai standar MySQL Statment dalam transaksi data yaitu:
 * Create, Read, Update & Delete (atau disingkat CRUD)
 * Ditambahkan beberapa fungsi dari masing2 statment yang terintegrasi dengan crudHandler
 * Class Database tersebut didefinisikan di __construct untuk semua ruang lingkup class crudHandler
 *
 * @since v.2.0
 * @author Ofan Ebob
 * @copyright 2014
 *
 * Fungsi dalam Class:
 * ------------------------------
 * showData()
 * getDataWhere()
 * updateData()
 * insertData()
 * deleteData()
 */
if(!class_exists('OfanCoreFramework')) die('Error Class OfanCoreFramework');

class crudHandler extends OfanCoreFramework
{
    private $db;

    function __construct($cluster=false)
    {
        /**
         * Isi cluster merupakan nama berupa string
         * yang akan di cocokan ke file sheet.json
         * di dalam variable $Manifest dbHandler() (yang di ambil dari serverHandler())
         */
        if(!is_string($cluster)) exit(false);

        try
        {
            if(!class_exists('dbHandler')) parent::_handler(array('dbHandler'));
            $this->db = parent::_handler('db')->connect(array('dbname'=>$cluster));
        }
        catch(Exception $e)
        {
            $this->db = false;
        }
    }

    public function thisDB()
    {
        return $this->db;
    }
    
    private function _limitHandler_($type=null, $query, $data=null)
    {
        if(is_null($data))
        {
            switch($type)
            {
                case 'exec':
                    return $query;
                break;
                case 'query':
                    return "";
                break;
                default:
                    return null;
                break;
            }
        }
        else
        {
            if(preg_match('/[\,]/', $data))
            {
                $trimLimit = explode(',', $data);

                $limitParam = array(':min',':max');
                $limitData = array($limitParam[0]=>(int)$trimLimit[0], $limitParam[1]=>(int)$trimLimit[0]);
            }
            else
            {
                $limitParam = array(':min');
                $limitData = array($limitParam[0]=>(int)$data);
            }

            switch($type)
            {
                case 'exec':
                    $query = $query;
                    foreach($limitData as $paramKey=>$paramValue)
                    {
                        $query->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
                        $query->execute();
                    }
                    return $query;
                break;
                case 'query':
                    $limitParamMinMax = join($limitParam,', ');
                    return " LIMIT $limitParamMinMax";
                break;
                default:
                    return null;
                break;
            }
        }
    }



    private function _groupHandler_($data)
    {
       if(count($data) > 0)
        {
            $row = isset($data['row']) ? $data['row'] : null;
            return (is_null($row) ? "" : " GROUP BY $row");
        }
        else
        {
            return "";
        }
    }



    private function _orderHandler_($data)
    {
       if(count($data) > 0)
        {
            if(isset($data['row']))
            {
                $row = $data['row'];
                $sort = isset($data['sort']) ? $data['sort'] : 'ASC';
                return " ORDER BY $row $sort";
            }
            else
            {
                return "";
            }
        }
        else
        {
            return "";
        }
    }


    
    public function min($table, $field)  
    {
        if($table && $field)
        return $this->db->single("SELECT min(" . $field . ")" . " FROM " . $table);
    }
    public function max($table, $field)  
    {
        if($table && $field)
        return $this->db->single("SELECT max(" . $field . ")" . " FROM " . $table);
    }
    public function minmax($table, $field, $where=array())  
    {
        if($table && $field)

        $WHERE = '';
        if(count($where) > 0 && !is_null($table))
        {
            $fieldColumnWhere = '';
            foreach($where as $colWhereKey=>$columnWhere)
            {
                $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $columnWhere;
            }

            $WHERE = 'WHERE '.join($fieldColumnWhere,' AND ');
        }

        $sql = "SELECT min($field), max($field) FROM $table $WHERE";
        //var_dump($sql);
        $q = $this->db->query($sql);
        if(!$q) return false;
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function avg($table, $field)  
    {
        if($table && $field)
        return $this->db->single("SELECT avg(" . $field . ")" . " FROM " . $table);
    }
    public function sum($table, $field)  
    {
        if($table && $field)
        return $this->db->single("SELECT sum(" . $field . ")" . " FROM " . $table);
    }

    public function count($table, $field, $paramDataWhere=array())  
    {
        if($table && $field)

        $WHERE = '';
        if(count($paramDataWhere) > 0 && !is_null($table))
        {
            $fieldColumnWhere = '';
            foreach($paramDataWhere as $colWhereKey=>$columnWhere)
            {
                $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
            }

            $WHERE = 'WHERE '.join($fieldColumnWhere,' AND ');
        }

        $sql = "SELECT count($field) FROM $table $WHERE";
        $q = $this->db->prepare($sql);
        $q->execute();
        return $q->fetchColumn();
    }


    
    /**
     * showData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function showRowSchema($schema=null, $table=null, $select='column_name')
    {
        if(is_null($schema)) return false;
        if(is_null($table)) return false;
        if($this->db === false) return false;
        $sql = "SELECT $select FROM information_schema.columns WHERE table_schema='$schema' AND table_name='$table'";
        $q = $this->db->query($sql) or die("failed!");
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }


    
    /**
     * showData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function showData($table=null, $select=null, $orderBy=null, $paramLimit=null)
    {
        if($this->db === false) return false;
            
        if(!is_null($table))
        {
            $select = is_null($select) ? '*' : $select;
            $order = is_null($orderBy) ? '' : self::_orderHandler_($orderBy);
            if(is_array($table))
            {
                $joinSQL = '';
                foreach($table as $tblKey=>$tblValue)
                {
                    $joinSQL[] = '"'.'SELECT '.$select.' FROM '.$tblValue.'"'.';';
                }

                $sql = join($joinSQL,'');
            }
            else
            {
                $sql = "SELECT $select FROM $table";
            }
            $limitHandlerQuery = self::_limitHandler_('query', null, $paramLimit);
            $fieldAll = join($sql,' AND ').$limitHandlerQuery;

            $sql = $fieldAll.$order;

            $q = $this->db->query($sql) or die("failed!");
            return $q->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return false;
        }
    }


    
    /**
     * getDataWhere()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */
    public function getDataWhere($table=null, $select=null, $paramDataWhere=array(), $paramLimit=null)
    {
        if($this->db === false) return false;
            
        if(count($paramDataWhere) > 0 && !is_null($table))
        {
            $fieldColumnWhere = '';
            foreach($paramDataWhere as $colWhereKey=>$columnWhere)
            {
                $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
            }

            $limitHandlerQuery = self::_limitHandler_('query', null, $paramLimit);
            $fieldAll = join($fieldColumnWhere,' AND ').$limitHandlerQuery;

            $select = is_null($select) ? "*" : (is_array($select) ? join($select,',') : $select);
            $sql = "SELECT $select FROM $table WHERE $fieldAll";
            $q = $this->db->prepare($sql);

            foreach($paramDataWhere as $paramKey=>$paramValue)
            {
                if(is_integer($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
                }
                elseif(is_string($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_STR);
                }
                else
                {
                    $q->bindValue($paramKey, $paramValue);
                }
                $q->execute();
            }

            $query = $q;
            $query = self::_limitHandler_('exec', $query, $paramLimit);
            //var_dump($query);//die();

            if($query)
            {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    
    /**
     * getDataWhereOR()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function getDataWhereOR($table=null, $select=null, $paramWhereOR=array(), $paramLimit=null)
    {
        if($this->db === false) return false;
            
        if(count($paramWhereOR) > 0 && !is_null($table))
        {
            $fieldColumnWhere = '';
            foreach($paramWhereOR as $colWhereKey=>$columnWhere)
            {
                $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
            }

            $limitHandlerQuery = self::_limitHandler_('query', null, $paramLimit);
            $fieldAll = join($fieldColumnWhere,' OR ').$limitHandlerQuery;

            $select = is_null($select) ? "*" : (is_array($select) ? join($select,',') : $select);
            $sql = "SELECT $select FROM $table WHERE $fieldAll";
            $q = $this->db->prepare($sql);

            foreach($paramWhereOR as $paramKey=>$paramValue)
            {
                if(is_integer($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
                }
                elseif(is_string($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_STR);
                }
                else
                {
                    $q->bindValue($paramKey, $paramValue);
                }
                $q->execute();
            }

            $query = $q;
            $query = self::_limitHandler_('exec', $query, $paramLimit);

            if($query)
            {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    
    /**
     * getDataMultiWhere()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function getDataWhereIn($table=null, $select=null, $paramWhereIn=array(), $where=array(), $orderBy=null)
    {
        if($this->db === false) return false;
        if(count($paramWhereIn) > 0 && !is_null($table))
        {
            $fieldColumnWhere = $paramWhereIn[0];
            $paramWhereValue = array_values($paramWhereIn[1]);
            $valueParams = [];
            for($i=0;$i<count($paramWhereValue);$i++)
            {
                if(is_numeric($paramWhereValue[$i]))
                {
                    $valueParams[$i] = (int)$paramWhereValue[$i];
                }
                elseif(is_string($paramWhereValue[$i]))
                {
                    $paramAlias = $paramWhereValue[$i];
                    $valueParams[$i] = "'".$paramAlias."'";
                }
                else
                {
                    $valueParams[$i] = $paramWhereValue[$i];
                }
            }

            $wherePushColumn = '';
            if(count($where) > 0)
            {
                $wherePush = [];
                foreach($where as $whereKey=>$whereVal)
                {
                    array_push($wherePush, "$whereKey = '$whereVal'");
                }
                $wherePushColumn = join(" AND ",$wherePush);
                $wherePushColumn = count($paramWhereIn) > 0 ? $wherePushColumn." AND " : $wherePushColumn;
            }
            $fieldColumnWhere = $wherePushColumn.$fieldColumnWhere;
            
            $order = is_null($orderBy) ? '' : self::_orderHandler_($orderBy);
            $fieldColumnWhereSet = " IN(".join($valueParams,',').")";
            $fieldAll = $fieldColumnWhere.$fieldColumnWhereSet.$order;

            $select = is_null($select) ? "*" : (is_array($select) ? join($select,',') : $select);
            $sql = "SELECT $select FROM $table WHERE $fieldAll";
            $q = $this->db->query($sql);
            //var_dump($fieldAll);die();
            if($q)
            {
                return $q->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    
    /**
     * getDataFilter()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function getDataFilter($table=null, $select=null, $paramWhere=array(), $paramGroup=array(), $paramOrder=array(), $paramLimit=null, $paramKeywords=array(), $paramMaxMinWhere=array())
    {
        if($this->db === false) return false;
            
        if(!is_null($table))
        {
            $filterParam = array();
            if(!is_null($paramGroup)) if(count($paramGroup) > 0) $filterParam[0] = self::_groupHandler_($paramGroup);
            if(!is_null($paramOrder)) if(count($paramOrder) > 0) $filterParam[1] = self::_orderHandler_($paramOrder);
            if(!is_null($paramLimit)) if(count($paramLimit) > 0) $filterParam[2] = self::_limitHandler_('query', null, $paramLimit);
            
            $fieldColumnLikes = '';
            if(count($paramKeywords) > 0)
            {
                $fieldColumnLike = '';
                foreach($paramKeywords as $colWhereKey=>$columnWhere)
                {
                    $fieldColumnLike[] = "lower(".str_replace(':', '', $colWhereKey).") LIKE ". $colWhereKey;
                }
                $fieldColumnLikes = ' AND ('.(join($fieldColumnLike,' OR ')).')';
            }

            $fieldColumnMinMax = '';
            if(count($paramMaxMinWhere) >= 1)
            {
                $fieldColumnMin = '';
                if(isset($paramMaxMinWhere['min']))
                {
                    $fieldColumnMin = str_replace(':', '', $paramMaxMinWhere['min'][0]) . " >= ". $paramMaxMinWhere['min'][0].'_min';
                    $fieldColumnMin = ' AND '.$fieldColumnMin;
                }

                $fieldColumnMax = '';
                if(isset($paramMaxMinWhere['max']))
                {
                    $fieldColumnMax = str_replace(':', '', $paramMaxMinWhere['max'][0]) . " <= ". $paramMaxMinWhere['max'][0].'_max';
                    $fieldColumnMax = ' AND '.$fieldColumnMax;
                }

                $fieldColumnSpecial = '';
                if(isset($paramMaxMinWhere['special']))
                {
                    $fieldColumnSpecial = str_replace(':', '', $paramMaxMinWhere['special']['row']) . " ".$paramMaxMinWhere['special']['operator']." ". $paramMaxMinWhere['special']['row'].'_special';
                    $fieldColumnSpecial = ' AND '.$fieldColumnSpecial;
                }
                $fieldColumnMinMax = $fieldColumnMin.$fieldColumnMax.$fieldColumnSpecial;
            }

            if(count($paramWhere) > 0)
            {
                $fieldColumnWhere = '';
                foreach($paramWhere as $colWhereKey=>$colWhereValue)
                {
                    if(is_null($colWhereValue))
                    {
                        $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " IS NULL";
                    }
                    else
                    {
                        $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
                    }
                }

                $fieldColumnWhereGrup = join($fieldColumnWhere," AND ");
                $fieldColumnWhereGrup = $fieldColumnLikes == '' ? $fieldColumnWhereGrup : "(".$fieldColumnWhereGrup.")";
                $fieldAll = (" WHERE ".$fieldColumnWhereGrup.$fieldColumnLikes.$fieldColumnMinMax.(join($filterParam, '')));
            }
            else
            {
                $fieldAll = $fieldColumnLikes.$fieldColumnMinMax.(join($filterParam, ''));
            }

            $select = is_null($select) ? "*" : (is_array($select) ? join($select,',') : $select);
            $tableWhereOrderGroupLimit = $table.$fieldAll;
            $q = $this->db->prepare("SELECT $select FROM $tableWhereOrderGroupLimit");
            
            //var_dump($q);die();
            if(count($paramKeywords) > 0)
            {
                foreach($paramKeywords as $paramKey=>$paramValue)
                {
                    $keywords = "%".$paramValue."%";
                    $q->bindParam($paramKey, $keywords, PDO::PARAM_STR);
                    //$q->execute();
                }
            }

            if(count($paramMaxMinWhere) >= 1)
            {
                //var_dump($paramMaxMinWhere);
                if(isset($paramMaxMinWhere['min']))
                {
                    $q->bindValue($paramMaxMinWhere['min'][0].'_min', $paramMaxMinWhere['min'][1], PDO::PARAM_INT);
                }

                if(isset($paramMaxMinWhere['max']))
                {
                    $q->bindValue($paramMaxMinWhere['max'][0].'_max', $paramMaxMinWhere['max'][1], PDO::PARAM_INT);
                }

                if(isset($paramMaxMinWhere['special']))
                {
                    $q->bindValue($paramMaxMinWhere['special']['row'].'_special', $paramMaxMinWhere['special']['val'], PDO::PARAM_INT);
                }
            }

            if(count($paramWhere) > 0)
            {
                foreach($paramWhere as $paramKey=>$paramValue)
                {
                    //var_dump($paramKey, $paramValue);
                    if(is_integer($paramValue))
                    {
                        $q->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
                    }
                    elseif(is_string($paramValue))
                    {
                        $q->bindValue($paramKey, $paramValue, PDO::PARAM_STR);
                    }
                    else
                    {
                        $q->bindValue($paramKey, $paramValue);
                    }
                }
            }
            
            $q->execute();
            //var_dump($q);

            $query = $q;
            $query = self::_limitHandler_('exec', $query, $paramLimit);

            if($query)
            {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    
    /**
     * searchData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function searchData($table, $paramKeywords=array(), $select=null, $paramLimit=null)
    {
        if($this->db === false) return false;
            
        if(count($paramKeywords) > 0 && !is_null($table))
        {
            $fieldColumnLike = '';
            foreach($paramKeywords as $colWhereKey=>$columnWhere)
            {
                $fieldColumnLike[] = "lower(".str_replace(':', '', $colWhereKey).") LIKE ". $colWhereKey;
            }

            $limitHandlerQuery = self::_limitHandler_('query', null, $paramLimit);
            $fieldAll = (join($fieldColumnLike,' OR ').$limitHandlerQuery);

            $select = is_null($select) ? "*" : (is_array($select) ? join($select,',') : $select);
            $sql = "SELECT $select FROM $table WHERE $fieldAll";
            $q = $this->db->prepare($sql);
            
            foreach($paramKeywords as $paramKey=>$paramValue)
            {
                $keywords = "%".$paramValue."%";
                $q->bindParam($paramKey, $keywords, PDO::PARAM_STR);
                $q->execute();
            }

            $query = $q;
            $query = self::_limitHandler_('exec', $query, $paramLimit);
            //var_dump($sql);die();
            if($query)
            {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    /**
     * multiUpdateIncrement()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     * 
     * Sample Query:
     * UPDATE table as aliasTable 
     * SET column_a = c.column_a, column_b = c.column_b
     * WHERE (13,2,3,4) as
     */
    public function multiUpdateIncrement($table=null, $operator='+', $paramWhere=array(), $paramSet=array())
    {
        if($this->db === false) return false;
        if(count($paramSet) >  0 && !is_null($table))
        {
            $fieldColumnSet = '';
            foreach($paramSet as $colSetKey=>$columnSet)
            {
                $columnKeyColonless = str_replace(':', '', $colSetKey);
                $fieldColumnSet[] = "$columnKeyColonless = $columnKeyColonless $operator $colSetKey";
            }
            $fieldColumnSet = join($fieldColumnSet,', ');
            $allParamArray = $paramSet;
            
            $fieldColumnWhere = '';
            if(count($paramWhere) > 0)
            {
                foreach($paramWhere as $colWhereKey=>$columnWhere)
                {
                    $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
                }
                $fieldColumnWhere = join($fieldColumnWhere,' AND ');
                $allParamArray = array_merge($paramWhere, $paramSet);
            }

            $sql = "UPDATE $table SET $fieldColumnSet WHERE $fieldColumnWhere";
            $q = $this->db->prepare($sql);
            //var_dump($sql);die();
            $q->execute($allParamArray);
            if($q == true){return true;}
            else{return false;}
        }
        else
        {
            return false;
        }
    }


    /**
     * updateDicrease()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */
    public function updateIncrement($table=null, $operator='+', $paramWhere=array(), $paramSet=array(), $paramLike=array())
    {
        if($this->db === false) return false;
            
        if(count($paramSet) >  0 && !is_null($table))
        {
            $fieldColumnSet = '';
            foreach($paramSet as $colSetKey=>$columnSet)
            {
                $columnKeyColonless = str_replace(':', '', $colSetKey);
                $fieldColumnSet[] = "$columnKeyColonless = $columnKeyColonless $operator $colSetKey";
            }
            $fieldColumnSet = join($fieldColumnSet,', ');
            $allParamArray = $paramSet;
            
            $fieldColumnWhere = '';
            if(count($paramWhere) > 0)
            {
                foreach($paramWhere as $colWhereKey=>$columnWhere)
                {
                    $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
                }
                $fieldColumnWhere = join($fieldColumnWhere,' AND ');
                $allParamArray = array_merge($paramWhere, $paramSet);
            }

            if(count($paramLike) > 0)
            {
                $paramLikesJoin = '';
                $paramLikePush = [];
                foreach($paramLike as $parLikeKey=>$parLikeVal)
                {
                    $paramLikesJoin[] = str_replace(':', '', $parLikeKey) . " LIKE ". $parLikeKey;
                    $paramLikePush[$parLikeKey] = "%$parLikeVal%";
                }
                $fieldColumnLike = join($paramLikesJoin,' OR ');
                $fieldColumnWhere = count($paramWhere) > 0 ? (
                    count($paramLike) > 0 ? $fieldColumnWhere." AND (".$fieldColumnLike.")" : $fieldColumnWhere
                ) : $fieldColumnLike;
                $allParamArray = array_merge($allParamArray, $paramLikePush);
            }

            $sql = "UPDATE $table SET $fieldColumnSet WHERE $fieldColumnWhere";
            $q = $this->db->prepare($sql);
            //var_dump($sql);die();
            $q->execute($allParamArray);

            if($q == true){return true;}
            else{return false;}
        }
        else
        {
            return false;
        }
    }


    /**
     * updateData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */
    public function updateData($table=null, $paramWhere=array(), $paramSet=array(), $paramLike=array())
    {
        if($this->db === false) return false;
            
        if(count($paramSet) >  0 && !is_null($table))
        {

            $fieldColumnSet = '';
            foreach($paramSet as $colSetKey=>$columnSet)
            {
                $fieldColumnSet[] = str_replace(':', '', $colSetKey) . " = ". $colSetKey;
            }
            $fieldColumnSet = join($fieldColumnSet,', ');
            $allParamArray = $paramSet;
            
            $fieldColumnWhere = '';
            if(count($paramWhere) > 0)
            {
                foreach($paramWhere as $colWhereKey=>$columnWhere)
                {
                    $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
                }
                $fieldColumnWhere = join($fieldColumnWhere,' AND ');
                $allParamArray = array_merge($paramWhere, $paramSet);
            }

            if(count($paramLike) > 0)
            {
                $paramLikesJoin = '';
                $paramLikePush = [];
                foreach($paramLike as $parLikeKey=>$parLikeVal)
                {
                    $paramLikesJoin[] = str_replace(':', '', $parLikeKey) . " LIKE ". $parLikeKey;
                    $paramLikePush[$parLikeKey] = "%$parLikeVal%";
                }
                $fieldColumnLike = join($paramLikesJoin,' OR ');
                $fieldColumnWhere = count($paramWhere) > 0 ? (
                    count($paramLike) > 0 ? $fieldColumnWhere." AND (".$fieldColumnLike.")" : $fieldColumnWhere
                ) : $fieldColumnLike;
                $allParamArray = array_merge($allParamArray, $paramLikePush);
            }

            $sql = "UPDATE $table SET $fieldColumnSet WHERE $fieldColumnWhere";
            $q = $this->db->prepare($sql);
            //var_dump($sql);die();
            $q->execute($allParamArray);

            if($q == true){return true;}
            else{return false;}
        }
        else
        {
            return false;
        }
    }


    
    /**
     * insertData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function insertData($table, $paramSet=array())
    {
        if($this->db === false) return false;
            
        if(count($paramSet) > 0 && !is_null($table))
        {
            $fieldColumnSet = '';
            $fieldColumnRow = '';
            foreach($paramSet as $colSetKey=>$colSetValue)
            {
                $fieldColumnSet[] = str_replace(':', '', $colSetKey);
                $fieldColumnRow[] = $colSetKey;
            }
            $fieldColumnSet = join($fieldColumnSet,',');
            $fieldColumnRow = join($fieldColumnRow,',');

            $sql = "INSERT INTO $table($fieldColumnSet) VALUES ($fieldColumnRow)";
            $q = $this->db->prepare($sql);

            foreach($paramSet as $paramKey=>$paramValue)
            {
                if(is_integer($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
                }
                elseif(is_string($paramValue))
                {
                    $q->bindValue($paramKey, $paramValue, PDO::PARAM_STR);
                }
                else
                {
                    $q->bindValue($paramKey, $paramValue);
                }
                $q->execute();
            }
            //var_dump($sql);
            if($q){return $q->rowCount();}
            else{return false;}
        }
        else
        {
            return false;
        }
        //Set fungsi untuk menghitung ulang data $q->rowCount();
    }


    
    /**
     * insertMultiData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */
    public function insertMultiple($tableName, $data=array())
    {
        if($this->db === false) return false;
        if(count($data) > 0 && !is_null($tableName))
        {
            $rowsSQL = array();
            $toBind = array();
            $columnNames = array_keys($data[0]);
            foreach($data as $arrayIndex => $row)
            {
                $params = array();
                foreach($row as $columnName => $columnValue)
                {
                    $param = ':' . $columnName . $arrayIndex;
                    $params[] = $param;
                    $toBind[$param] = $columnValue; 
                }

                $paramImplode = implode(', ', $params);
                $rowsSQL[] = "($paramImplode)";
            }

            $colName = implode(', ', $columnNames);
            $rowQuery = implode(', ', $rowsSQL);
            // Gunakan preffix public. untuk mengakses database jika tidak memiliki Primary Key
            $sql = "INSERT INTO $tableName ($colName) VALUES $rowQuery";
            $q = $this->db->prepare($sql);
            $qspdo = $q;
            foreach($toBind as $param => $val)
            {
                //$qspdo = $qspdo;
                if(is_integer($val))
                {
                    $qspdo->bindValue($param, $val, PDO::PARAM_INT);
                }
                elseif(is_string($val))
                {
                    $qspdo->bindValue($param, $val, PDO::PARAM_STR);
                }
                else
                {
                    $qspdo->bindValue($param, $val);
                }
            }
            $qspdo->execute();
            //var_dump($qspdo);die();
            if($qspdo){return $qspdo->rowCount();} // RowCount ini harus dirubah karena terlalu dinamis untuk multi USER
            else{return false;}
        }
        else
        {
            return false;
        }
    }



    /**
     * deleteData()
     * Fungsi publik
     *
     * @since v.2.0
     * @author Ofan Ebob
     * @copyright 2017
     */    
    public function deleteData($table=null, $paramWhere=array())
    {
        if($this->db === false) return false;
            
        if(count($paramWhere) > 0 && !is_null($table))
        {
            $fieldColumnWhere = '';
            foreach($paramWhere as $colWhereKey=>$columnWhere)
            {
                $fieldColumnWhere[] = str_replace(':', '', $colWhereKey) . " = ". $colWhereKey;
            }
            $fieldColumnWhere = join($fieldColumnWhere,' AND ');

            $sql = "DELETE FROM $table WHERE $fieldColumnWhere";
            $q = $this->db->prepare($sql);
            $q->execute($paramWhere);
            //var_dump($q);
            if($q){return $q->rowCount();}
            else{return false;}
        }
        else
        {
            return false;
        }
        //Set fungsi untuk menghitung ulang data $q->rowCount();
    }
}
?>