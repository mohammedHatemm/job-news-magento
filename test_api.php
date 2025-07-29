<?php

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

require __DIR__ . '/vendor/autoload.php';

$params = $_SERVER;

// Set the working directory to the Magento root
try {
  $bootstrap = Bootstrap::create(BP, $params);
  $objectManager = $bootstrap->getObjectManager();

  // Set area code
  $state = $objectManager->get('Magento\Framework\App\State');
  $state->setAreaCode('adminhtml');

  // Enable error reporting
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // Test category creation
  $categoryData = [
    'category_name' => 'Test Category',
    'category_description' => 'Test Description',
    'category_status' => 1,
    'parent_ids' => []
  ];

  $logger = $objectManager->get('Psr\Log\LoggerInterface');
  $logger->info('Starting category creation test', ['data' => $categoryData]);

  try {
    $category = $objectManager->create('News\Manger\Api\Data\CategoryInterfaceFactory')->create();
    $category->setData($categoryData);

    $logger->info('Category object created', ['category' => $category->getData()]);

    $repository = $objectManager->get('News\Manger\Api\CategoryRepositoryInterface');
    $result = $repository->save($category);

    $logger->info('Category saved successfully', ['id' => $result->getCategoryId()]);
    echo "Category created successfully! ID: " . $result->getCategoryId() . "\n";
  } catch (\Exception $e) {
    $logger->error('Error saving category', [
      'message' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);

    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
  }
} catch (\Exception $e) {
  echo "Bootstrap Error: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
  echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
