<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models\Quote\Address;
use Doctrine\Tests\Models\Quote\Group;
use Doctrine\Tests\Models\Quote\User;

/**
 * @group DDC-1845
 * @group DDC-1885
 */
class DDC1885Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    /**
     * @var \Doctrine\Tests\Models\Quote\User
     */
    private $user;

    protected function setUp()
    {
        $this->enableQuotes = true;
        
        parent::setUp();

        try {
            $this->_schemaTool->createSchema(
                [
                $this->_em->getClassMetadata(User::class),
                $this->_em->getClassMetadata(Group::class),
                $this->_em->getClassMetadata(Address::class),
                ]
            );
        } catch(\Exception $e) {
        }

        $user           = new User();
        $user->name     = "FabioBatSilva";
        $user->email    = "fabio.bat.silva@gmail.com";
        $user->groups[] = new Group('G 1');
        $user->groups[] = new Group('G 2');
        $this->user     = $user;

        // Create
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();

    }

    public function testCreateRetrieveUpdateDelete()
    {
        $user   = $this->user;
        $g1     = $user->getGroups()->get(0);
        $g2     = $user->getGroups()->get(1);

        $u1Id   = $user->id;
        $g1Id   = $g1->id;
        $g2Id   = $g2->id;

        // Retrieve
        $user = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(2, $user->groups);

        $g1 = $user->getGroups()->get(0);
        $g2 = $user->getGroups()->get(1);

        self::assertInstanceOf(Group::class, $g1);
        self::assertInstanceOf(Group::class, $g2);

        $g1->name = 'Bar 11';
        $g2->name = 'Foo 22';

        // Update
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();

        $user = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        // Delete
        $this->_em->remove($user);

        $this->_em->flush();
        $this->_em->clear();

        self::assertNull($this->_em->find(User::class, $u1Id));
        self::assertNull($this->_em->find(Group::class, $g1Id));
        self::assertNull($this->_em->find(Group::class, $g2Id));
    }

    public function testRemoveItem()
    {
        $user   = $this->user;
        $u1Id   = $user->id;
        $user   = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(2, $user->groups);
        self::assertInstanceOf(Group::class, $user->getGroups()->get(0));
        self::assertInstanceOf(Group::class, $user->getGroups()->get(1));

        $user->getGroups()->remove(0);

        // Update
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();

        $user = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(1, $user->getGroups());
    }

    public function testClearAll()
    {
        $user   = $this->user;
        $u1Id   = $user->id;
        $user   = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(2, $user->groups);
        self::assertInstanceOf(Group::class, $user->getGroups()->get(0));
        self::assertInstanceOf(Group::class, $user->getGroups()->get(1));

        $user->getGroups()->clear();

        // Update
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();

        $user = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(0, $user->getGroups());
    }

    public function testCountExtraLazy()
    {
        $user   = $this->user;
        $u1Id   = $user->id;
        $user   = $this->_em->find(User::class, $u1Id);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals('FabioBatSilva', $user->name);
        self::assertEquals($u1Id, $user->id);

        self::assertCount(2, $user->groups);
        self::assertInstanceOf(Group::class, $user->getGroups()->get(0));
        self::assertInstanceOf(Group::class, $user->getGroups()->get(1));
    }
}
