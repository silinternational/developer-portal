<?php
namespace Sil\DevPortal\tests\unit;

class EventTest extends \CDbTestCase
{
    public $fixtures = array(
        'events' => 'Event',
    );
    
    public function testFixtureDataValidity()
    {
        foreach ($this->events as $fixtureName => $fixtureData) {
            /* @var $event \Event */
            $event = $this->events($fixtureName);
            $this->assertTrue($event->delete(), sprintf(
                'Could not delete event fixture %s: %s',
                $fixtureName,
                print_r($event->getErrors(), true)
            ));
            $eventOnInsert = new \Event();
            $eventOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($eventOnInsert->save(), sprintf(
                'Event fixture "%s" (ID %s) does not have valid data: %s',
                $fixtureName,
                $eventOnInsert->event_id,
                var_export($eventOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testLog()
    {
        $this->markTestIncomplete('Test(s) not yet written.');
    }
    
    public function testRules_created_noValueGiven()
    {
        // Arrange:
        $event = new \Event();
        $event->attributes = array(
            'description' => 'A unit test created a dummy event.',
        );
        
        // Act:
        $result = $event->save();
        
        // Assert:
        $this->assertTrue($result, sprintf(
            'Failed to create an event for testing how the created field is handled: %s',
            json_encode($event->getErrors())
        ));
        $event->refresh();
        $this->assertNotNull(
            $event->created,
            'Failed to provide a created value when none was given during the '
            . 'creation of an Event.'
        );
    }
    
    public function testRules_created_valueOverridden()
    {
        // Arrange:
        $originalCreatedValue = '2016-06-27 13:47:04';
        $event = new \Event();
        $event->attributes = array(
            'description' => 'A unit test created a dummy event.',
            'created' => $originalCreatedValue,
        );
        
        // Act:
        $result = $event->save();
        
        // Assert:
        $this->assertTrue($result, sprintf(
            'Failed to create an event for testing how the created field is handled: %s',
            json_encode($event->getErrors())
        ));
        $event->refresh();
        $this->assertNotEquals(
            $originalCreatedValue,
            $event->created,
            'Failed to override the given created value.'
        );
    }
    
    public function testRules_created_notAutoChangedOnUpdate()
    {
        // Arrange:
        $event = $this->events('hasDescriptionAndCreated');
        $originalCreatedValue = $event->created;
        
        // Act:
        $event->description = 'A revised description';
        $result = $event->save();
        
        // Assert:
        $this->assertTrue($result, sprintf(
            'Failed to save an existing Event (to test how "created" is handled): %s',
            json_encode($event->getErrors())
        ));
        $event->refresh();
        $this->assertEquals(
            $originalCreatedValue,
            $event->created,
            'Incorrectly changed the created date on subsequent save.'
        );
    }
}
