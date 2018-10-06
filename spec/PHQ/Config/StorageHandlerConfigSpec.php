<?php

namespace spec\PHQ\Config;

use PHQ\Config\StorageHandlerConfig;
use PhpSpec\ObjectBehavior;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Jobs\IJob;
use PHQ\Storage\IQueueStorageConfigurable;
use PHQ\Storage\IQueueStorageHandler;
use Prophecy\Argument;

class StorageHandlerConfigSpec extends ObjectBehavior
{
    function let(){
        $this->beConstructedWith(StorageHandlerTest::class, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StorageHandlerConfig::class);
    }

    function it_should_throw_an_error_if_specified_class_is_not_a_valid_queue_class()
    {
        $this->beConstructedWith(InvalidStorageHandlerTest::class, []);
        $this->shouldThrow(ConfigurationException::class)->during('getStorage');
    }

    function it_should_be_able_to_create_a_storage_handler_instance_from_configuration()
    {
        $this->getStorage()->shouldBeAnInstanceOf(StorageHandlerTest::class);
    }

    function it_should_configure_a_storage_handler_if_it_needs_configuration(StorageHandlerConfigurableTest $handler){
        $this->beConstructedWith(StorageHandlerConfigurableTest::class, [
            "a" => 1
        ]);
        $this->setHandlerInstance($handler);
        $handler->init(["a" => 1])->shouldBeCalled();
        $this->getStorage()->shouldBeAnInstanceOf(StorageHandlerConfigurableTest::class);
    }
}

class InvalidStorageHandlerTest{

}

class StorageHandlerConfigurableTest implements IQueueStorageHandler, IQueueStorageConfigurable {

    public function get($id): ?JobDataset
    {
        //Stub
    }

    public function enqueue(IJob $job): bool
    {
        //Stub
    }

    public function getNext(): ?JobDataset
    {
        //Stub
    }

    public function init(array $options): void
    {
        //Stub
    }
}

class StorageHandlerTest implements IQueueStorageHandler
{
    public function get($id): ?JobDataset
    {
        //Stub
    }

    public function enqueue(IJob $job): bool
    {
        //Stub
    }

    public function getNext(): ?JobDataset
    {
        //Stub
    }
}