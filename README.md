# Commission Task (Homework assignment solution)

This is a PHP CLI application for calculating commission fees for financial operations. It processes transaction data from a CSV file, applies exchange rates and commission rules, and outputs the calculated commission for each operation.

## Requirements

- PHP 7.4 or higher
- Composer
- Acccess key provided by https://exchangeratesapi.io

- **Optional**: Vagrant & VirtualBox for isolated development


## Installation

1. Clone the repository:   
git clone https://github.com/sebybuga/commission-task.git
cd commission-task

2. Install PHP dependencies:
composer install

3. Add your input CSV file in the project root or use the sample input.csv

4. Edit the config/api.json file to set your API key (exchange_api_key) 


## Usage

php commission-task input.csv

-use exchange rates from homework assignment:
php script.php input.csv --use-exchange-test

-show input (and output) values:
php script.php input.csv --show-input-values

- **Optional**: Usage with Vagrant - run the following commands to set up a Vagrant virtual machine:
vagrant up
vagrant ssh
cd /home/vagrant/commission-task
php commission-task input.csv

## Running Test
Run scriptTest to verify that input values produce the expected output values:
./vendor/phpunit/phpunit/phpunit tests/scriptTest.php

Run test for a specific test class:
./vendor/phpunit/phpunit/phpunit tests/Service/CommissionCalculatorServiceTest.php


## Config files
config/currencies.json – Default currency decimal places
config/commissions.json – Commission rate config
config/api.json – API settings for exchange rates
config/test-exchange-rates.json – Exchange rate values for testing

*Adding a new currency: update currencies.json and test-exchange-rates.json with the new currency and its exchange rate.

## Exchange Rates
By default, the app fetches live currency exchange rates from the URL specified in api.json. 
For testing, local exchange rate values are used by adding --use-exchange-test option after input file name.

## Validation
Each input row is validated:
-Correct number of fields
-Valid date, user ID, operation type, user type
-Valid currency format
-Valid amount
