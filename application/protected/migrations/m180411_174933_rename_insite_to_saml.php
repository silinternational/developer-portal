<?php

class m180411_174933_rename_insite_to_saml extends CDbMigration
{
    public function safeUp()
    {
        // Add 'SAML' as an auth_provider option.
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('SAML','Insite','Google','GitHub','Bitbucket','TEST') DEFAULT NULL"
        );
        
        $this->replaceAuthProviderValues('Insite', 'SAML');
        
        // Remove 'Insite' as an auth_provider option.
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('SAML','Google','GitHub','Bitbucket','TEST') DEFAULT NULL"
        );
    }
    
    protected function replaceAuthProviderValues($oldValue, $newValue)
    {
        $this->execute(
            'UPDATE {{user}} '
            . 'SET `auth_provider` = :new_auth_provider_value '
            . 'WHERE `auth_provider` = :old_auth_provider_value',
            [
                ':old_auth_provider_value' => $oldValue,
                ':new_auth_provider_value' => $newValue,
            ]
        );
    }

    public function safeDown()
    {
        // Add 'Insite' back as an auth_provider option.
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('SAML','Insite','Google','GitHub','Bitbucket','TEST') DEFAULT NULL"
        );
        
        $this->replaceAuthProviderValues('SAML', 'Insite');
        
        // Remove 'SAML' as an auth_provider option.
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('Insite','Google','GitHub','Bitbucket','TEST') DEFAULT NULL"
        );
    }
}
