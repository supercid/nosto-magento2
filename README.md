# Nosto module for Magento 2

Increase your conversion rate and average order value by delivering your
customers personalized product recommendations throughout their shopping
journey.

Nosto allows you to deliver every customer a personalized shopping experience
through recommendations based on their unique user behavior - increasing
conversion, average order value and customer retention as a result.

[http://nosto.com](http://nosto.com/)

## Installing

The preferred way of installing the extension is via [Composer](https://getcomposer.org/). If you don't have composer installed yet you can get it by following [these instructions](https://getcomposer.org/doc/00-intro.md). It's recommended to install composer globally. You will also need public key and private key from Magento Marketplace or Magento Connect in order to install packages to Magento 2 via Composer. Please follow these instructions to get public key and private key http://devdocs.magento.com/guides/v2.1/install-gde/prereq/connect-auth.html. Once you have composer installed you can install Nosto extension (nosto/module-nostotagging).

For complete installation instructions please see our [Wiki](https://github.com/Nosto/nosto-magento2/wiki)

## Functional Testing Using MFTF

In order to run the tests for this extension, you need t have at least Magento 2.2 with Magento Functional Testing Framework installed as a composer dependency.
The tests are located under the `Test` directory.

Refer to Magento DevDocs in order to prepare Magento.
[M2 DevDocs](https://devdocs.magento.com/guides/v2.2/magento-functional-testing-framework/release-2/getting-started.html)

Currently there is a bug in the MFTF (2.2.0) that does not allow the tests to run from the `vendor` folder. <br>
You need to copy the nosto extension into the `%MagentoRootInstallation%/app/code/Nosto/Tagging` directory.

To run the suite, head to `%MagentoRootInstallation%/dev/tests/acceptance` and run the following command: <br>
```bash 
vendor/bin/robo generate:tests && vendor/bin/codecept run functional --group nosto
```

## License

Open Software License ("OSL") v3.0
