<?php

namespace spec\PHQ\Config;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PhpSpec\ObjectBehavior;
use PHQ\Config\PHQConfig;
use PHQ\Config\StorageHandlerConfig;
use spec\TestObjects\TestQueueStorage;

class PHQConfigSpec extends ObjectBehavior
{
    /**
     * @var vfsStreamDirectory
     */
    protected $dir;
    protected $config = [
        "environment" => "test",
        "storage" => [
            "handler" => TestQueueStorage::class,
            "options" => [
                "test" => [
                    "test" => "value"
                ]
            ]
        ],
    ];

    /**
     * @var vfsStreamFile
     */
    protected $configFile;

    function let()
    {
        $this->dir = vfsStream::setup("config");

        $this->configFile = vfsStream::newFile("phqconf.php");

        $this->setConfigData($this->config);

        $this->dir->addChild($this->configFile);

        $this->beConstructedWith(vfsStream::url("config"));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PHQConfig::class);
    }

    function it_should_be_able_to_load_a_config_file()
    {
        $this->load();
        $this->getRawConfig()->shouldReturn($this->config);
    }

    function it_should_be_able_to_create_a_storage_config_object()
    {
        $this->load();
        $this->getStorageConfig()->shouldBeAnInstanceOf(StorageHandlerConfig::class);
    }

    function it_should_setup_the_config_with_the_correct_environment_options()
    {
        $this->load();

        /**
         * @var StorageHandlerConfig
         */
        $config = $this->getStorageConfig()->getWrappedObject();

        expect($config->class)->shouldBe($this->config['storage']['handler']);
        expect($config->options['test'])->shouldBe('value');
    }

    function it_should_use_existing_storage_config_if_already_created()
    {
        $config = new StorageHandlerConfig("abc", []);
        $this->beConstructedWith(vfsStream::url("config"), $config);

        $this->load();
        $this->getStorageConfig()->shouldBe($config);
    }

    function it_should_return_null_when_storage_config_is_missing()
    {
        $this->setConfigData([]);
        $this->load();
        $this->getStorageConfig()->shouldBe(null);
    }

    private function setConfigData($data): void
    {
        $this->configFile->withContent("<?php return " . var_export($data, true) . ";");
    }
}
