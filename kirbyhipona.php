<?php

class DataSource
{
    private $data;

    public function __construct()
    {
        // Simulating data retrieval from the data source
        $this->data = [
            ['name' => 'Item 1', 'category' => 'Category A'],
            ['name' => 'Item 2', 'category' => 'Category B'],
            ['name' => 'Item 3', 'category' => 'Category A'],
            ['name' => 'Item 4', 'category' => 'Category C'],
            ['name' => 'Item 5', 'category' => 'Category D'],
            ['name' => 'Item 6', 'category' => 'Category D'],
            ['name' => 'Item 7', 'category' => 'Category C'],
            ['name' => 'Item 8', 'category' => 'Category C'],
        ];
    }

    public function getAllItems()
    {
        // TODO: Implement error handling for data retrieval

        return $this->data;
    }

    public function getAllCategories()
    {
        // Extract all unique categories from the data
        $categories = array_unique(array_column($this->data, 'category'));

        // Sort the categories alphabetically
        sort($categories);

        return $categories;
    }
}

class DataProcessor
{
    private $dataSource;
    private $logger;

    public function __construct(DataSource $dataSource, Logger $logger)
    {
        $this->dataSource = $dataSource;
        $this->logger = $logger;
    }

    public function getFilteredAndSortedItems($category)
    {
        try {
            $items = $this->dataSource->getAllItems();

            // Filter items based on the provided category
            $filteredItems = array_filter($items, function ($item) use ($category) {
                return $item['category'] === $category;
            });

            // Sort items alphabetically by name
            usort($filteredItems, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $filteredItems;
        } catch (Exception $e) {
            // Log the error
            $this->logger->logError($e->getMessage());

            // Rethrow the exception to be handled at a higher level
            throw $e;
        }
    }
}

class Logger
{
    public function logError($errorMessage)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $errorMessage" . PHP_EOL;
        file_put_contents("error.log", $logMessage, FILE_APPEND);
    }
}

// Using dependency injection to instantiate objects
$dataSource = new DataSource();
$logger = new Logger();
$dataProcessor = new DataProcessor($dataSource, $logger);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Code Test</title>
</head>

<body>
    <form method="post">
        <label for="categories">Choose a category:</label>
        <select name="categories" id="categories">
            <?php
            $categories = $dataSource->getAllCategories();

            foreach ($categories as $category) {
                $selected = isset($_POST['categories']) && $_POST['categories'] === $category ? 'selected' : '';
                echo "<option value='$category' $selected>$category</option>";
            }
            ?>
        </select>
        <button type="submit">Submit</button>
    </form>
    <?php
    try {
        if (isset($_POST['categories'])) {
            $selectedCategory = $_POST['categories'];

            // Retrieving and displaying filtered and sorted items
            $filteredItems = $dataProcessor->getFilteredAndSortedItems($selectedCategory);

            if (!empty($filteredItems)) {
                echo "<ul>";
                foreach ($filteredItems as $item) {
                    echo "<li>" . $item['name'] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "No items found for the selected category.";
            }
        }
    } catch (Exception $e) {
        // Handle the exception appropriately
        // TODO: Implement error handling for the application
        $logger->logError($e->getMessage());
    }
    ?>

</body>

</html>