<?php

require_once "TestDummyBMSkillTestStinger.php";
require_once "TestDummyBMAttSkillTesting.php";

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-21 at 15:12:24.
 */
class BMAttackSkillTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMAttackSkill
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = TestDummyBMAttSkillTesting::get_instance();
        $this->object->reset();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMAttackSkill::validate_attack
     * @todo   Implement testValidate_attack().
     */
    public function testValidate_attack()
    {
        $game = new TestDummyGame;

        $sk = $this->object;

        $die1 = BMDie::create(6);
        $die1->value = 6;

        $die2 = BMDie::create(6);
        $die2->value = 2;

        $die3 = BMDie::create(6);
        $die3->value = 4;

        $die4 = BMDie::create(6);
        $die4->value = 2;

        $die5 = BMDie::create(6);
        $die5->value = 1;

        // Naturally created during the flow of the game, need to make
        // by hand here
        $sk->make_hit_table();

        // Won't find anything with an empty hit table

        $this->assertFalse($sk->validate_attack($game, array($die2, $die3), array($die1)));

        // proper setup
        $sk->reset();
        foreach (array($die1, $die2, $die3, $die4, $die5) as $d) {
            // Add the dice to the game
            $sk->add_die($d);
            $game->attackerAllDieArray[] = $d;
        }
        $sk->make_hit_table();



        // Basic error testing
        $this->assertFalse($sk->validate_attack($game, array(), array()));
        $this->assertFalse($sk->validate_attack($game, array($die1), array()));
        $this->assertFalse($sk->validate_attack($game, array(), array($die1)));
        $this->assertFalse($sk->validate_attack($game, array($die2), array($die3, $die4)));

        // Successful attacks
        $target = BMDie::create(20);
        $def = array($target);

        $target->value = 6;
        $this->assertTrue($sk->validate_attack($game, array($die1), $def));

        $target->value = 8;
        $this->assertTrue($sk->validate_attack($game, array($die1, $die2), $def));

        $target->value = 8;
        $this->assertTrue($sk->validate_attack($game, array($die2, $die3, $die4), $def));

        $target->value = 14;
        $this->assertTrue($sk->validate_attack($game, array($die1, $die2, $die3, $die4), $def));

        $target->value = 12;
        $this->assertTrue($sk->validate_attack($game, array($die1, $die3, $die4), $def));
        $this->assertTrue($sk->validate_attack($game, array($die1, $die2, $die3), $def));

        $target->value = 15;
        $this->assertTrue($sk->validate_attack($game, array($die1, $die2, $die3, $die4, $die5), $def));

        // Failures

        // Can't take subsets
        for ($i=1; $i <= 14; $i++) {
            $target->value = $i;
            $this->assertFalse($sk->validate_attack($game, array($die1, $die2, $die3, $die4, $die5), $def));
        }

        // Have to add up
        $target->value = 8;
        $this->assertFalse($sk->validate_attack($game, array($die1, $die5), $def));

        $target->value = 11;
        $this->assertFalse($sk->validate_attack($game, array($die1, $die3), $def));

        $target->value = 20;
        $this->assertFalse($sk->validate_attack($game, array($die1), $def));

        $target->value = 1;
        $this->assertFalse($sk->validate_attack($game, array($die2), $def));


        // Fun with helpers!
        $die1->value = 3;
        $die2->value = 6;
        $die3->value = 9;
        $die4->value = 6;
        $die5->value = 1;
        $die5->add_skill("AVTesting", "TestDummyBMSkillAVTesting");

        // reset the hit table
        $sk->make_hit_table();

        $target->value = 4;
        $this->assertTrue($sk->validate_attack($game, array($die1, $die5), $def));
        $this->assertTrue($sk->validate_attack($game, array($die1), $def));
        $target->value = 2;
        $this->assertTrue($sk->validate_attack($game, array($die1), $def));

        // Not when the helper's involved
        $target->value = 5;
        $this->assertFalse($sk->validate_attack($game, array($die1, $die5), $def));

        // multi-value dice
        $die1->value = 6;
        $die1->add_skill("TestStinger", "TestDummyBMSkillTestStinger");
        $die2->value = 6;
        $die3->value = 6;
        $die4->value = 6;
        $die5->value = 6;
        $die5->remove_skill("AVTesting");

        // reset the hit table
        $sk->make_hit_table();

        for ($i = 1; $i <= 5; $i++) {
            $target->value = $i;
            $this->assertTrue($sk->validate_attack($game, array($die1), $def));
            $this->assertFalse($sk->validate_attack($game, array($die2), $def));
            $target->value = $i + 12;
            $this->assertTrue($sk->validate_attack($game, array($die1, $die2, $die3), $def));
            $this->assertFalse($sk->validate_attack($game, array($die1, $die4), $def));

        }
    }

    /**
     * @covers BMAttackSkill::find_attack
     * @depends testValidate_attack
     * @todo   Implement testFind_attack().
     */
    public function testFind_attack()
    {
        $game = new TestDummyGame;

        $sk = $this->object;

        // we find nothing when there are no attackers
        $this->assertFalse($sk->find_attack($game));

        // Load some dice into the attack.
        $die1 = BMDie::create(6);
        $die1->value = 6;

        $die2 = BMDie::create(6);
        $die2->value = 2;

        $die3 = BMDie::create(6);
        $die3->value = 4;

        $die4 = BMDie::create(6);
        $die4->value = 5;

        $sk->reset();

        $sk->add_die($die1);
        $sk->add_die($die2);


        // we find nothing when there are no defenders
        $this->assertFalse($sk->find_attack($game));

        // Basic attacks
        $game->defenderAllDieArray[] = $die3;

        // 6, 2 vs 4
        $this->assertFalse($sk->find_attack($game));

        // success
        $die3->value = 2;
        $this->assertTrue($sk->find_attack($game));

        $die3->value = 6;
        $this->assertTrue($sk->find_attack($game));

        $die3->value = 8;
        $this->assertTrue($sk->find_attack($game));

        // Find targets among more options
        $game->defenderAllDieArray[] = $die4;

        $this->assertTrue($sk->find_attack($game));

        // Attacks with helpers
        $sk->reset();

        $die5 = BMDie::create(6,
                    array("TestDummyBMSkillAVTesting" => "AVTesting"));
        $die5->value = 1;

        $sk->add_die($die1);
        $game->attackerAllDieArray[] = $die1;
        $sk->add_die($die5);
        $game->attackerAllDieArray[] = $die5;
        $sk->add_die($die2);
        $game->attackerAllDieArray[] = $die2;

        $die3->value = 20;
        $this->assertTrue($sk->find_attack($game));
        $die4->value = 20;
        $this->assertFalse($sk->find_attack($game));
        $die4->value = 4;
        $this->assertFalse($sk->find_attack($game));

        // Multi-value dice
        $sk->reset();

        $die5->remove_skill("AVTesting");
        $die5->value = 6;

        $die1->value = 6;
        $die1->add_skill("TestStinger", "TestDummyBMSkillTestStinger");

        $die2->value = 4;

        $sk->add_die($die1);
        $sk->add_die($die5);
        $sk->add_die($die2);

        $die3->value = 20;
        $die4->value = 20;
        $this->assertFalse($sk->find_attack($game));

        $die3->value = 6;
        $this->assertTrue($sk->find_attack($game));
        $die3->value = 10;
        $this->assertTrue($sk->find_attack($game));
        $die3->value = 16;
        $this->assertTrue($sk->find_attack($game));

        for ($i = 1; $i <= 5; $i++) {
            $die3->value = $i;
            $this->assertTrue($sk->find_attack($game));
            $die3->value = $i+6;
            $this->assertTrue($sk->find_attack($game));
            $die3->value = $i+10;
            $this->assertTrue($sk->find_attack($game));
        }


    }

    /**
     * @covers BMAttackSkill::calculate_contributions
     * @todo   Implement testCalculate_contributions().
     */
    public function testCalculate_contributions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}

