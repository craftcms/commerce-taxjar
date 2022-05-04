<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="TaxJar icon"></p>

<h1 align="center">TaxJar for Craft Commerce</h1>

This plugin provides a tax integration between [Craft Commerce](https://craftcms.com/commerce) and [TaxJar](https://www.taxjar.com/).

It replaces Craft Commerce’s built-in tax engine, offloading the work of managing tax rates, tax zones, and tax categories to TaxJar.

## Requirements

This plugin requires Craft Commerce (Pro edition) 4.0 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “TaxJar”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/commerce-taxjar

# tell Craft to install the plugin
php craft install/plugin commerce-taxjar
```

## Configuration & Setup

### Step 1: Configure the plugin

The plugin has two TaxJar API connection settings. To set them, follow these steps: 

1. Define new `TAXJAR_API_KEY` and `TAXJAR_SANDBOX` environment variables in your `.env` file (or wherever you manage your environment variables).

   ```
   # Set to your TaxJar API key
   TAXJAR_API_KEY="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
   
   # Set to 0 or 1 depending on whether the sandbox API endpoint should be used
   TAXJAR_SANDBOX="1"
   ```

2. Create a `config/commerce-taxjar.php` file with `apiKey` and `useSandbox` settings, which pull in the environment variables.

   ```php
   <?php
   
   return [
       'apiKey' => getenv('TAXJAR_API_KEY'),
       'useSandbox' => (bool)getenv('TAXJAR_SANDBOX'),
   ];
   ```

### Step 2: Sync your tax categories

To sync your tax categories with TaxJar, go to Commerce → Tax → Tax Categories in your control panel, and click on the “Sync” button.

Once the sync is complete, you will be free to edit the synced tax categories, and assign them to products as you normally would.

> **Warning:** Don’t change the tax category handles, as that will cause duplicate tax categories to appear the next time you sync. 

### Step 4: Check your store location

Go to Commerce → Store Settings → Store Location, and ensure that everything is set correctly there. The plugin will use this info to populate the _from_ address when getting tax info from TaxJar.

## Usage

Once the plugin is set up properly, tax adjustments will be added to new orders automatically, per the line items’ tax categories.

When the TaxJar API is queried for tax info, the full API response is JSON-encoded and stored in the tax adjustment’s `sourceSnapshot` value.

---

## Feature Roadmap

- [x] Pull live rates for a cart from TaxJar.
- [x] Replace the built in tax engine in Craft Commerce
- [x] Sync tax categories with TaxJar
- [ ] Sync completed orders with TaxJar for reporting purposes
- [ ] Sync customers TaxJar for reporting and tax exemption purposes
