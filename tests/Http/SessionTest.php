<?php
namespace Test\Http;

use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\NamespaceResolver;
use WhiteBox\Http\Session;

class SessionTest extends PHPUnit{
    public $session_start;
    public $session_abort;
    public $session_status;

    public $local__session_start;
    public $local__session_abort;
    public $local__session_status;

    public static $sstatus = PHP_SESSION_NONE;
    public static function setSessionStatus(int $status){
        self::$sstatus = $status;
    }

    public static function mock(string $name, callable $functor, string $namespace = ""){
        return (new MockBuilder())
            ->setName($name)
            ->setFunction($functor)
            ->setNamespace($namespace)
            ->build();
    }

    public function setUp(){
        self::$sstatus = PHP_SESSION_NONE;

        $this->local__session_start = self::mock(
            "session_start",
            function(array $options=[]){
                //For test we assume that sessions are enabled
                if(session_status() !== PHP_SESSION_ACTIVE)
                    SessionTest::setSessionStatus(PHP_SESSION_ACTIVE);
            },
            __NAMESPACE__
        );

        $this->session_start = self::mock(
            "session_start",
            function(array $options=[]){
                //For test we assume that sessions are enabled
                if(session_status() !== PHP_SESSION_ACTIVE)
                    SessionTest::setSessionStatus(PHP_SESSION_ACTIVE);
            },
            NamespaceResolver::getFrom(Session::class)
        );

        $this->local__session_abort = self::mock(
            "session_abort",
            function(){
                if(session_status() === PHP_SESSION_ACTIVE)
                    SessionTest::setSessionStatus(PHP_SESSION_NONE);
            },
            __NAMESPACE__
        );
        $this->session_abort = self::mock(
            "session_abort",
            function(){
                if(session_status() === PHP_SESSION_ACTIVE)
                    SessionTest::setSessionStatus(PHP_SESSION_NONE);
            },
            NamespaceResolver::getFrom(Session::class)
        );

        $this->local__session_status = self::mock(
            "session_status",
            function(){
                return SessionTest::$sstatus;
            },
            __NAMESPACE__
        );
        $this->session_status = self::mock(
            "session_status",
            function(){
                return SessionTest::$sstatus;
            },
            NamespaceResolver::getFrom(Session::class)
        );
    }

    /**
     * @after
     */
    public function tearDownAfterEach(){
        Session::stop();

        $this->session_start->disable();
        $this->session_abort->disable();
        $this->session_status->disable();

        $this->local__session_status->disable();
        $this->local__session_abort->disable();
        $this->local__session_start->disable();

        $_SESSION = [];
    }

    /**
     * @before
     */
    public function setupBeforeEach(){
        //echo("status: " . self::$sstatus);
        $this->setUp();

        $this->session_status->enable();
        $this->session_start->enable();
        $this->session_abort->enable();

        $this->local__session_status->enable();
        $this->local__session_start->enable();
        $this->local__session_abort->enable();

        $_SESSION = [];
    }

    ///////////////////////////////////////////////////////////////

    /**
     * @test
     * @covers Session::getStatus
     */
    public function statusGetsTheCorrectStatus(){
        self::assertEquals(
            session_status(),
            Session::getStatus(),
            "Even though getStatus() is a wrapper around session_status, it does not retrieve the correct status"
        );
    }

    /**
     * @test
     * @depends statusGetsTheCorrectStatus
     * @covers Session::isStarted
     */
    public function isStartedWhenStarted(){
        session_start();

        self::assertTrue(
            Session::isStarted(),
            "Even though the session was started manually, isStarted isn't true"
        );
    }

    /**
     * @test
     * @depends isStartedWhenStarted
     * @covers Session::isStarted
     */
    public function isStartedWhenNot(){
        session_abort();
        self::assertFalse(
            Session::isStarted(),
            "Even though no session is started, isStarted is true"
        );
    }

    /**
     * @test
     * @depends isStartedWhenNot
     * @covers Session::start
     */
    public function startStartsSession(){
        Session::start();
        self::assertTrue(
            Session::isStarted(),
            "Even though we started a session, the session is not active"
        );
    }

    /**
     * @test
     * @covers Session::start
     * @depends startStartsSession
     */
    public function isStartedAfterStarted(){
        Session::start();

        self::assertTrue(
            Session::isStarted(),
            "Even though we wanted to start a session, it has not started"
        );
    }

    /**
     * @test
     * @depends isStartedAfterStarted
     * @covers Session::stop
     */
    public function stopStopsIfStarted(){
        Session::start();
        Session::stop();

        self::assertEquals(
            PHP_SESSION_NONE,
            Session::getStatus(),
            "Even though a session was started and stop() was called, the session is still running"
        );
    }

    /**
     * @test
     * @covers Session::stop
     * @depends stopStopsIfStarted
     */
    public function stopDoesntChangeIfStopped(){
        session_abort();
        Session::stop();

        self::assertEquals(
            PHP_SESSION_NONE,
            Session::getStatus(),
            "Even though the session was stopped, stop() changed the session's status"
        );
    }

    /**
     * @test
     * @covers Session::ensureStarted
     * @depends stopDoesntChangeIfStopped
     */
    public function ensureStartedStartsWhenNotStarted(){
        Session::stop();
        Session::ensureStarted();

        self::assertEquals(
            PHP_SESSION_ACTIVE,
            Session::getStatus(),
            "Even though there's no session started, ensureStarted didn't start the session"
        );
    }

    /**
     * @test
     * @covers Session::ensureStarted
     * @depends ensureStartedStartsWhenNotStarted
     */
    public function ensureStartedDoesntChangeWhenStarted(){
        Session::start();
        Session::ensureStarted();

        self::assertEquals(
            PHP_SESSION_ACTIVE,
            Session::getStatus(),
            "Even though the session is already started, ensureStarted modified the session's status"
        );
    }

    /**
     * @test
     * @depends ensureStartedDoesntChangeWhenStarted
     * @covers Session::get
     */
    public function getUndefinedReturnsDefault(){
        $default = 12;

        Session::start();
        self::assertEquals(
            $default,
            Session::get("UNDEFINED Key BeCause Spaces & BS", $default),
            "Despite the facts that the key doesn't exist and a default is provided, the said default value isn't returned"
        );
    }

    /**
     * @test
     * @depends getUndefinedReturnsDefault
     * @covers Session::get
     */
    public function getUndefinedNoDefaultReturnsNull(){
        Session::start();
        self::assertNull(
            Session::get("UNDEFINED Key BeCause Spaces & BS"),
            "Even though the key doesn't exists in the session and the fact that there's no default value provided, the method doesn't return null"
        );
    }

    /**
     * @test
     * @depends getUndefinedNoDefaultReturnsNull
     * @covers Session::get
     */
    public function getGetsCorrect(){
        Session::start();
        $key = "key";
        $value = "value";
        $_SESSION[$key] = serialize($value);

        self::assertEquals(
            $value,
            Session::get($key),
            "Even though the key exists, the retrieved value isn't correct"
        );
    }

    /**
     * @test
     * @depends getGetsCorrect
     * @covers Session::set
     */
    public function setWorks(){
        Session::start();

        $key = "key";
        $value = "value";
        Session::set($key, $value);

        self::assertEquals(
            $value,
            Session::get($key),
            "Even though the value was set, the retrieved value is incorrect"
        );
    }

    /**
     * @test
     * @depends setWorks
     * @covers Session::delete
     */
    public function deleteWorks(){
        Session::start();

        $key ="key";
        Session::set($key, "value");
        Session::delete($key);

        self::assertFalse(
            isset($_SESSION[$key]),
            "Even though the key has been deleted, it is already set"
        );
    }
}