<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\InstallerBundle\Behat;

use Sylius\Bundle\InstallerBundle\Command\SetupCommand;
use Sylius\Bundle\ResourceBundle\Behat\DefaultContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * @author Magdalena Banasiak <magdalena.banasiak@lakion.com>
 */
class CliContext extends DefaultContext
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var DialogHelper
     */
    private $dialog;

    /**
     * @var SetupCommand
     */
    private $command;

    /**
     * @var array
     */
    private $inputChoices = array(
        'currency' => '',
        'name' => ' Name',
        'surname' => ' Surname',
        'e-mail' => ' test@email.com',
        'password' => ' pswd',
        'confirmation' => ' pswd'
    );

    /**
     * @When /^I run a command "([^"]+)"$/
     */
    public function iRunACommand($name, $parameters = array())
    {
        $this->application = new Application($this->getKernel());
        $this->application->add(new SetupCommand());

        $this->command = $this->application->find($name);
        $this->tester = new CommandTester($this->command);

        $this->iExecuteCommandWithInputChoices($name, $parameters);
    }

    /**
     * @Then /^I should see output "(.+)"$/
     */
    public function iShouldSeeOutput($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->tester->getDisplay());
    }

    /**
     * @Given I do not provide a currency
     */
    public function iDoNotProvideCurrency()
    {
        $this->inputChoices['currency'] = "";
    }

    /**
     * @Given I provide currency :code
     */
    public function iProvideCurrency($code)
    {
        $this->inputChoices['currency'] = $code;
    }

    /**
     * @Given I provide full administrator data
     */
    public function iProvideFullAdministratorData()
    {
        $this->inputChoices['name'] = "AdminName";
        $this->inputChoices['surname'] = "AdminSurname";
        $this->inputChoices['e-mail'] = "test@admin.com";
        $this->inputChoices['password'] = "pswd1$";
        $this->inputChoices['confirmation'] = $this->inputChoices['password'];
    }

    /**
     * @param $input
     * @return resource
     */
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    private function iExecuteCommandWithInputChoices($name, $parameters = array())
    {
        $fullParameters = array_merge(array('command' => $name), $parameters);
        $this->dialog = $this->command->getHelper('dialog');
        $inputString = join("\n", $this->inputChoices);
        $this->dialog->setInputStream($this->getInputStream($inputString."\n"));

        $this->tester->execute($fullParameters);
    }
}
