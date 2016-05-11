<?php

class DbResetCommand extends CConsoleCommand
{
    public function run($args)
    {
        if(count($args) < 1){
            $this->usageError('You must supply the database connectionID as an arguement. Example: ./yii dbreset testdb');
        } elseif (!isset (Yii::app()->$args[0])){
            $this->usageError($args[0].' does not appear to be a valid database connection id');
        }
        $tables = Yii::app()->$args[0]->createCommand('show tables')->queryAll(true);
        $tableNames = array();
        foreach($tables as $row){
            $key = array_keys($row);
            $tableNames[] = $row[$key[0]];
        }
        if(count($tableNames) === 0){
            echo "No tables to drop.\n";
        }
        foreach($tableNames as $table){
            echo "Dropping table $table...";
            Yii::app()->$args[0]->createCommand('SET foreign_key_checks = 0; drop table `'.$table.'`')->execute();
            echo "done.\n";
        }
        
        echo "Running migrations...\n";
        $migrateCmdPath = Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 
                'cli' . DIRECTORY_SEPARATOR . 'commands';
        $cmd = new CConsoleCommandRunner();
        $cmd->addCommands($migrateCmdPath);
        $cmd->run(array('yiic','migrate','--connectionID='.$args[0],'--interactive=0','--migrationTable=yii_migrations'));
        echo "Completed migrations.\n";
        
        echo "DbReset completed.\n";
    }
}