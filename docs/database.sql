-- ============================================================
-- Costa Resin & Construction — Esquema MySQL (generado 2026-11-16)
-- Base: esquema completo de `storage/framework/local-preview.sqlite`
-- (69 migraciones aplicadas). SIN DATOS — los datos los crea
-- el instalador web (migraciones + InstallSeeder) o `php artisan db:seed`.
-- Importar en phpMyAdmin o: mysql -u USUARIO -p NOMBRE_BD < database.sql
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `accounting_accounts` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `account_type` VARCHAR(255) not null,
  `is_active` tinyint(1) not null default '1',
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_tokens` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` VARCHAR(255) not null,
  `tokenable_id` INT not null,
  `name` VARCHAR(255),
  `token` VARCHAR(255) not null,
  `abilities` TEXT,
  `expires_at` DATETIME,
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `actor_id` INT,
  `actor_label` VARCHAR(255),
  `event` VARCHAR(255) not null,
  `subject_type` VARCHAR(255),
  `subject_id` VARCHAR(255),
  `old_values` TEXT,
  `new_values` TEXT,
  `ip_address` VARCHAR(255),
  `user_agent` VARCHAR(255),
  `created_at` DATETIME not null default CURRENT_TIMESTAMP,
  FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_post_tag` (
  `blog_post_id` INT not null,
  `tag_id` INT not null,
  FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts`(`id`) on delete cascade,
  FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) on delete cascade,
  PRIMARY KEY (`blog_post_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_post_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `blog_post_id` INT not null,
  `locale` VARCHAR(255) not null,
  `title` VARCHAR(255) not null,
  `excerpt` VARCHAR(255),
  `content` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_posts` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) not null,
  `category_id` INT,
  `video_url` VARCHAR(255),
  `status` VARCHAR(255) check (`status` in ('draft', 'published')) not null default 'draft',
  `published_at` DATETIME,
  `is_featured` tinyint(1) not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `title` TEXT,
  `excerpt` TEXT,
  `content` TEXT,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(255) not null,
  `value` TEXT not null,
  `expiration` INT not null,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) not null,
  `owner` VARCHAR(255) not null,
  `expiration` INT not null,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT,
  `slug` VARCHAR(255) not null,
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `name` TEXT,
  `description` TEXT,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `category_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT not null,
  `locale` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `color_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `color_id` INT not null,
  `locale` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `colors` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) not null,
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `name` TEXT,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `content_blocks` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `page` VARCHAR(255) not null,
  `section` VARCHAR(255) not null,
  `key` VARCHAR(255) not null,
  `value` TEXT,
  `type` VARCHAR(255) not null default 'TEXT',
  `locale` VARCHAR(255) not null default 'es',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `email` VARCHAR(255) not null,
  `email_verified_at` DATETIME,
  `password` VARCHAR(255) not null,
  `phone` VARCHAR(255),
  `company` VARCHAR(255),
  `locale` VARCHAR(255) not null default 'es',
  `remember_token` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(255) not null,
  `connection` TEXT not null,
  `queue` TEXT not null,
  `payload` TEXT not null,
  `exception` TEXT not null,
  `failed_at` DATETIME not null default CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `unit` VARCHAR(255) not null default 'unit',
  `reorder_level` DECIMAL(10,2) not null default '0',
  `is_active` tinyint(1) not null default '1',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_locations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(255),
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `is_active` tinyint(1) not null default '1',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_movements` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `inventory_item_id` INT not null,
  `inventory_location_id` INT not null,
  `movement_type` VARCHAR(255) not null,
  `quantity_change` DECIMAL(10,2) not null,
  `balance_after` DECIMAL(10,2) not null,
  `reference` VARCHAR(255),
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME not null default (CURRENT_TIMESTAMP),
  `work_order_id` INT,
  `unit_cost` DECIMAL(10,2),
  `total_cost` DECIMAL(10,2),
  FOREIGN KEY (`created_by`) references users(`id`) on delete set null on update no action,
  FOREIGN KEY (`inventory_location_id`) references inventory_locations(`id`) on delete restrict on update no action,
  FOREIGN KEY (`inventory_item_id`) references inventory_items(`id`) on delete restrict on update no action,
  FOREIGN KEY (`work_order_id`) REFERENCES `mapscalculator_work_orders`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_purchase_order_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `purchase_order_id` INT not null,
  `inventory_item_id` INT not null,
  `inventory_location_id` INT not null,
  `quantity` DECIMAL(10,2) not null,
  `unit_cost` DECIMAL(10,2),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`purchase_order_id`) REFERENCES `inventory_purchase_orders`(`id`) on delete cascade,
  FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) on delete restrict,
  FOREIGN KEY (`inventory_location_id`) REFERENCES `inventory_locations`(`id`) on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_purchase_orders` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `number` VARCHAR(255) not null,
  `supplier_id` INT not null,
  `status` VARCHAR(255) not null default 'draft',
  `ordered_at` date,
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers`(`id`) on delete restrict,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_receipt_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `inventory_receipt_id` INT not null,
  `purchase_order_item_id` INT not null,
  `inventory_movement_id` INT not null,
  `quantity` DECIMAL(10,2) not null,
  `unit_cost` DECIMAL(10,2),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`inventory_receipt_id`) REFERENCES `inventory_receipts`(`id`) on delete cascade,
  FOREIGN KEY (`purchase_order_item_id`) REFERENCES `inventory_purchase_order_items`(`id`) on delete restrict,
  FOREIGN KEY (`inventory_movement_id`) REFERENCES `inventory_movements`(`id`) on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_receipts` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `purchase_order_id` INT not null,
  `reference` VARCHAR(255),
  `notes` TEXT,
  `received_by` INT,
  `received_at` DATETIME not null,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`purchase_order_id`) REFERENCES `inventory_purchase_orders`(`id`) on delete restrict,
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_reservations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `work_order_id` INT not null,
  `inventory_item_id` INT not null,
  `inventory_location_id` INT not null,
  `quantity` DECIMAL(10,2) not null,
  `consumed_quantity` DECIMAL(10,2) not null default '0',
  `status` VARCHAR(255) not null default 'active',
  `notes` TEXT,
  `created_by` INT,
  `released_by` INT,
  `released_at` DATETIME,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`work_order_id`) REFERENCES `mapscalculator_work_orders`(`id`) on delete restrict,
  FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) on delete restrict,
  FOREIGN KEY (`inventory_location_id`) REFERENCES `inventory_locations`(`id`) on delete restrict,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null,
  FOREIGN KEY (`released_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_suppliers` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `tax_id` VARCHAR(255),
  `email` VARCHAR(255),
  `phone` VARCHAR(255),
  `address` TEXT,
  `is_active` tinyint(1) not null default '1',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_valuation_balances` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `inventory_item_id` INT not null,
  `inventory_location_id` INT not null,
  `quantity` DECIMAL(10,2) not null default '0',
  `inventory_value` DECIMAL(10,2) not null default '0',
  `average_unit_cost` DECIMAL(10,2) not null default '0',
  `created_by` INT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) on delete restrict,
  FOREIGN KEY (`inventory_location_id`) REFERENCES `inventory_locations`(`id`) on delete restrict,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoice_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT not null,
  `quote_item_id` INT,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `quantity` DECIMAL(10,2) not null default '1',
  `unit_price` DECIMAL(10,2) not null default '0',
  `discount_percent` DECIMAL(10,2) not null default '0',
  `total_price` DECIMAL(10,2) not null default '0',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) on delete cascade,
  FOREIGN KEY (`quote_item_id`) REFERENCES `quote_items`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `quote_id` INT not null,
  `invoice_number` VARCHAR(255),
  `status` VARCHAR(255) not null default 'draft',
  `customer_name` VARCHAR(255) not null,
  `customer_email` VARCHAR(255),
  `customer_phone` VARCHAR(255),
  `company` VARCHAR(255),
  `billing_address` VARCHAR(255),
  `billing_city` VARCHAR(255),
  `billing_postal_code` VARCHAR(255),
  `currency` VARCHAR(255) not null default 'EUR',
  `subtotal` DECIMAL(10,2) not null default '0',
  `tax_percent` DECIMAL(10,2) not null default '0',
  `tax_amount` DECIMAL(10,2) not null default '0',
  `total` DECIMAL(10,2) not null default '0',
  `due_date` date,
  `issued_at` DATETIME,
  `paid_at` DATETIME,
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) on delete restrict,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `total_jobs` INT not null,
  `pending_jobs` INT not null,
  `failed_jobs` INT not null,
  `failed_job_ids` TEXT not null,
  `options` TEXT,
  `cancelled_at` INT,
  `created_at` INT not null,
  `finished_at` INT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(255) not null,
  `payload` TEXT not null,
  `attempts` INT not null,
  `reserved_at` INT,
  `available_at` INT not null,
  `created_at` INT not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_entries` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `entry_number` VARCHAR(255),
  `entry_date` date not null,
  `description` VARCHAR(255) not null,
  `status` VARCHAR(255) not null default 'draft',
  `source_type` VARCHAR(255),
  `source_id` INT,
  `reversal_of_id` INT,
  `reversed_by_entry_id` INT,
  `posted_at` DATETIME,
  `created_by` INT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`reversal_of_id`) REFERENCES `journal_entries`(`id`) on delete restrict,
  FOREIGN KEY (`reversed_by_entry_id`) REFERENCES `journal_entries`(`id`) on delete restrict,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_entry_lines` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `journal_entry_id` INT not null,
  `accounting_account_id` INT not null,
  `debit` DECIMAL(10,2) not null default '0',
  `credit` DECIMAL(10,2) not null default '0',
  `description` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries`(`id`) on delete cascade,
  FOREIGN KEY (`accounting_account_id`) REFERENCES `accounting_accounts`(`id`) on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_clients` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `email` VARCHAR(255),
  `phone` VARCHAR(255),
  `address` TEXT,
  `notes` TEXT,
  `portal_token` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `company` VARCHAR(255),
  `nif` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_drawing_layers` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `measurement_id` INT not null,
  `name` VARCHAR(255) not null,
  `type` VARCHAR(255) not null,
  `data` TEXT not null,
  `style` TEXT not null,
  `visible` tinyint(1) not null default '1',
  `locked` tinyint(1) not null default '0',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`measurement_id`) REFERENCES `mapscalculator_measurements`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_measurement_templates` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `name` VARCHAR(255) not null,
  `type` VARCHAR(255) not null,
  `default_settings` TEXT not null,
  `default_layers` TEXT not null,
  `is_system` tinyint(1) not null default '0',
  `is_public` tinyint(1) not null default '0',
  `usage_count` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_measurements` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT not null,
  `type` VARCHAR(255) not null,
  `label` VARCHAR(255),
  `geojson` TEXT,
  `polygon_points` TEXT,
  `calibration_line` TEXT,
  `reference_image_path` VARCHAR(255),
  `scale_factor` DECIMAL(10,2),
  `area_m2` DECIMAL(10,2) not null default '0',
  `perimeter_m` DECIMAL(10,2) not null default '0',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `map_layer` VARCHAR(255) not null default 'satellite',
  `map_bounds` TEXT,
  `map_zoom` DECIMAL(10,2),
  `map_center` TEXT,
  `drawing_mode` VARCHAR(255),
  `circles` TEXT,
  `lines` TEXT,
  `compasses` TEXT,
  `dragpins` TEXT,
  `geojson_features` TEXT,
  `export_data` TEXT,
  `synced` tinyint(1) not null default '0',
  `last_synced_at` DATETIME,
  `cloud_id` VARCHAR(255),
  `sync_conflicts` TEXT,
  `is_premium` tinyint(1) not null default '0',
  `capture_path` VARCHAR(255),
  FOREIGN KEY (`project_id`) REFERENCES `mapscalculator_projects`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_price_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `slug` VARCHAR(255) not null,
  `category` VARCHAR(255) not null,
  `unit` VARCHAR(255) not null default 'm2',
  `unit_price_m2` DECIMAL(10,2) not null default '0',
  `description` TEXT,
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_projects` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT not null,
  `name` VARCHAR(255) not null,
  `type` VARCHAR(255) not null default 'interior',
  `status` VARCHAR(255) not null default 'nuevo',
  `created_by` INT,
  `assigned_to` INT,
  `scheduled_date` date,
  `notes` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `default_map_layer` VARCHAR(255) not null default 'satellite',
  `default_map_bounds` TEXT,
  `default_map_zoom` DECIMAL(10,2),
  `default_map_center` TEXT,
  `share_token` VARCHAR(255),
  `is_public` tinyint(1) not null default '0',
  `shared_at` DATETIME,
  `default_measurement_settings` TEXT,
  `is_premium` tinyint(1) not null default '0',
  `synced` tinyint(1) not null default '0',
  `last_synced_at` DATETIME,
  FOREIGN KEY (`client_id`) REFERENCES `mapscalculator_clients`(`id`) on delete cascade,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_subscriptions` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT not null,
  `plan` VARCHAR(255) not null,
  `status` VARCHAR(255) not null,
  `provider` VARCHAR(255) not null,
  `provider_subscription_id` VARCHAR(255),
  `provider_customer_id` VARCHAR(255),
  `trial_ends_at` DATETIME,
  `starts_at` DATETIME,
  `ends_at` DATETIME,
  `cancelled_at` DATETIME,
  `metadata` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_sync_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT not null,
  `syncable_type` VARCHAR(255) not null,
  `syncable_id` INT not null,
  `action` VARCHAR(255) check (`action` in ('create', 'update', 'delete', 'sync')) not null,
  `status` VARCHAR(255) check (`status` in ('pending', 'synced', 'conflict', 'failed')) not null,
  `local_data` TEXT,
  `remote_data` TEXT,
  `conflict_resolution` TEXT,
  `error_message` TEXT,
  `synced_at` DATETIME,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_work_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `work_order_id` INT not null,
  `created_by` INT,
  `date` date not null,
  `type` VARCHAR(255) not null default 'general',
  `description` TEXT not null,
  `photo_path` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `client_uuid` VARCHAR(255),
  FOREIGN KEY (`work_order_id`) REFERENCES `mapscalculator_work_orders`(`id`) on delete cascade,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mapscalculator_work_orders` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT not null,
  `quote_id` INT,
  `status` VARCHAR(255) not null default 'agendada',
  `scheduled_start` date,
  `scheduled_end` date,
  `actual_start` date,
  `actual_end` date,
  `assigned_to` INT,
  `notes` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `labor_cost` DECIMAL(10,2) not null default '0',
  `other_cost` DECIMAL(10,2) not null default '0',
  `cost_notes` TEXT,
  FOREIGN KEY (`project_id`) REFERENCES `mapscalculator_projects`(`id`) on delete cascade,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) on delete set null,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `model_type` VARCHAR(255) not null,
  `model_id` INT not null,
  `uuid` VARCHAR(255),
  `collection_name` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `file_name` VARCHAR(255) not null,
  `mime_type` VARCHAR(255),
  `disk` VARCHAR(255) not null,
  `conversions_disk` VARCHAR(255),
  `size` INT not null,
  `manipulations` TEXT not null,
  `custom_properties` TEXT not null,
  `generated_conversions` TEXT not null,
  `responsive_images` TEXT not null,
  `order_column` INT,
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) not null,
  `batch` INT not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` INT not null,
  `model_type` VARCHAR(255) not null,
  `model_id` INT not null,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) on delete cascade,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` INT not null,
  `model_type` VARCHAR(255) not null,
  `model_id` INT not null,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) on delete cascade,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT not null,
  `product_id` INT,
  `product_variant_id` INT,
  `product_name` VARCHAR(255) not null,
  `variant_name` VARCHAR(255),
  `quantity` INT not null,
  `unit_price` DECIMAL(10,2) not null,
  `total_price` DECIMAL(10,2) not null,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) on delete cascade,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) on delete set null,
  FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(255) not null,
  `status` VARCHAR(255) not null default 'pending',
  `payment_status` VARCHAR(255) not null default 'unpaid',
  `stripe_payment_intent_id` VARCHAR(255),
  `stripe_checkout_session_id` VARCHAR(255),
  `subtotal` DECIMAL(10,2) not null,
  `shipping_cost` DECIMAL(10,2) not null default '0',
  `tax` DECIMAL(10,2) not null default '0',
  `total` DECIMAL(10,2) not null,
  `customer_email` VARCHAR(255) not null,
  `customer_name` VARCHAR(255) not null,
  `customer_phone` VARCHAR(255),
  `shipping_address_line_1` VARCHAR(255) not null,
  `shipping_address_line_2` VARCHAR(255),
  `shipping_city` VARCHAR(255) not null,
  `shipping_state` VARCHAR(255),
  `shipping_postal_code` VARCHAR(255) not null,
  `shipping_country` VARCHAR(255) not null default 'ES',
  `billing_address_line_1` VARCHAR(255),
  `billing_address_line_2` VARCHAR(255),
  `billing_city` VARCHAR(255),
  `billing_state` VARCHAR(255),
  `billing_postal_code` VARCHAR(255),
  `billing_country` VARCHAR(255),
  `tax_id` VARCHAR(255),
  `company_name` VARCHAR(255),
  `notes` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `stripe_refund_id` VARCHAR(255),
  `stock_review_refund_status` VARCHAR(255),
  `stock_review_refund_idempotency_key` VARCHAR(255),
  `stock_review_refund_amount_cents` INT,
  `stock_review_refund_requested_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `page_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT not null,
  `locale` VARCHAR(255) not null,
  `title` VARCHAR(255) not null,
  `content` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pages` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) not null,
  `is_active` tinyint(1) not null default '1',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `title` TEXT,
  `content` TEXT,
  `deleted_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) not null,
  `token` VARCHAR(255) not null,
  `created_at` DATETIME,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `guard_name` VARCHAR(255) not null,
  `group` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` VARCHAR(255) not null,
  `tokenable_id` INT not null,
  `name` VARCHAR(255) not null,
  `token` VARCHAR(255) not null,
  `abilities` TEXT,
  `last_used_at` DATETIME,
  `expires_at` DATETIME,
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT not null,
  `locale` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `short_description` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variant_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_variant_id` INT not null,
  `locale` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variants` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT not null,
  `slug` VARCHAR(255) not null,
  `sku` VARCHAR(255),
  `price` DECIMAL(10,2),
  `stock` INT not null default '0',
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `attributes` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `name` TEXT,
  `description` TEXT,
  `image_path` VARCHAR(255),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT not null,
  `slug` VARCHAR(255) not null,
  `price` DECIMAL(10,2) default '0',
  `compare_at_price` DECIMAL(10,2),
  `stock` INT not null default ('0'),
  `is_active` tinyint(1) not null default ('1'),
  `is_featured` tinyint(1) not null default ('0'),
  `sku` VARCHAR(255),
  `weight_kg` DECIMAL(10,2),
  `width_cm` DECIMAL(10,2),
  `height_cm` DECIMAL(10,2),
  `depth_cm` DECIMAL(10,2),
  `sort_order` INT not null default ('0'),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `is_coming_soon` tinyint(1) not null default ('0'),
  `name` TEXT,
  `short_description` TEXT,
  `description` TEXT,
  `video_path` VARCHAR(255),
  `image_path` VARCHAR(255),
  `video_url` VARCHAR(255),
  FOREIGN KEY (`category_id`) references categories(`id`) on delete cascade on update no action
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `project_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT not null,
  `locale` VARCHAR(255) not null,
  `title` VARCHAR(255) not null,
  `description` TEXT,
  `location` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `projects` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) not null,
  `video_url` VARCHAR(255),
  `is_featured` tinyint(1) not null default '0',
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `title` TEXT,
  `description` TEXT,
  `location` TEXT,
  `category` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quote_items` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `quote_id` INT not null,
  `product_id` INT,
  `product_variant_id` INT,
  `price_item_id` INT,
  `name` VARCHAR(255) not null,
  `description` TEXT,
  `quantity` DECIMAL(10,2) not null default '1',
  `unit_price` DECIMAL(10,2) not null default '0',
  `discount_percent` DECIMAL(10,2) not null default '0',
  `total_price` DECIMAL(10,2) not null default '0',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) on delete cascade,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) on delete set null,
  FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants`(`id`) on delete set null,
  FOREIGN KEY (`price_item_id`) REFERENCES `mapscalculator_price_items`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quote_payment_refunds` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `quote_payment_id` INT not null,
  `requested_by` INT,
  `stripe_refund_id` VARCHAR(255),
  `idempotency_key` VARCHAR(255) not null,
  `amount_cents` INT not null,
  `status` VARCHAR(255) not null default 'pending',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`quote_payment_id`) REFERENCES `quote_payments`(`id`) on delete restrict,
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quote_payments` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `quote_id` INT not null,
  `customer_id` INT,
  `payment_type` VARCHAR(255) not null,
  `amount_cents` INT not null,
  `currency` VARCHAR(255) not null default 'eur',
  `status` VARCHAR(255) not null default 'pending',
  `checkout_idempotency_key` VARCHAR(255) not null,
  `stripe_checkout_session_id` VARCHAR(255),
  `stripe_payment_intent_id` VARCHAR(255),
  `paid_at` DATETIME,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) on delete restrict,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) on delete set null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotes` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `email` VARCHAR(255),
  `phone` VARCHAR(255),
  `company` VARCHAR(255),
  `address` VARCHAR(255),
  `city` VARCHAR(255),
  `postal_code` VARCHAR(255),
  `service_type` VARCHAR(255) not null,
  `area_m2` DECIMAL(10,2),
  `description` TEXT,
  `status` VARCHAR(255) not null default ('new'),
  `admin_notes` TEXT,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `deleted_at` DATETIME,
  `items` TEXT,
  `quote_number` VARCHAR(255),
  `valid_until` date,
  `subtotal` DECIMAL(10,2) not null default ('0'),
  `tax_percent` DECIMAL(10,2) not null default ('0'),
  `tax_amount` DECIMAL(10,2) not null default ('0'),
  `total` DECIMAL(10,2) not null default ('0'),
  `terms_conditions` TEXT,
  `is_admin_created` tinyint(1) not null default ('0'),
  `sent_at` DATETIME,
  `approved_at` DATETIME,
  `rejected_at` DATETIME,
  `version` INT not null default ('1'),
  `parent_quote_id` INT,
  `project_id` INT,
  `pdf_path` VARCHAR(255),
  `deposit_percent` DECIMAL(10,2) not null default '30'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` INT not null,
  `role_id` INT not null,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) on delete cascade,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) on delete cascade,
  PRIMARY KEY (`permission_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `guard_name` VARCHAR(255) not null,
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) not null,
  `user_id` INT,
  `ip_address` VARCHAR(255),
  `user_agent` TEXT,
  `payload` TEXT not null,
  `last_activity` INT not null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group` VARCHAR(255) not null default 'general',
  `key` VARCHAR(255) not null,
  `value` TEXT,
  `type` VARCHAR(255) not null default 'TEXT',
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `simulator_finish_variants` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `simulator_finish_id` INT not null,
  `name` VARCHAR(255) not null,
  `base_color_hex` VARCHAR(255),
  `texture_color_path` VARCHAR(255),
  `texture_normal_path` VARCHAR(255),
  `texture_roughness_path` VARCHAR(255),
  `preview_image_path` VARCHAR(255),
  `chip_density` INT,
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`simulator_finish_id`) REFERENCES `simulator_finishes`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `simulator_finishes` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `type` VARCHAR(255) check (`type` in ('chips', 'solid', 'metallic')) not null,
  `price_per_m2` DECIMAL(10,2) not null,
  `currency` VARCHAR(255) not null default 'EUR',
  `is_active` tinyint(1) not null default '1',
  `sort_order` INT not null default '0',
  `created_at` DATETIME,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `simulator_quotes` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(255),
  `customer_email` VARCHAR(255),
  `customer_phone` VARCHAR(255),
  `simulator_room_template_id` INT,
  `simulator_finish_variant_id` INT not null,
  `target_surface` VARCHAR(255) not null,
  `area_m2` DECIMAL(10,2) not null,
  `price_per_m2_snapshot` DECIMAL(10,2) not null,
  `total_price` DECIMAL(10,2) not null,
  `status` VARCHAR(255) not null default ('pending'),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`simulator_finish_variant_id`) references simulator_finish_variants(`id`) on delete no action on update no action,
  FOREIGN KEY (`simulator_room_template_id`) references simulator_room_templates(`id`) on delete no action on update no action
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `simulator_room_templates` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `slug` VARCHAR(255) not null,
  `model_glb_path` VARCHAR(255),
  `configurable_meshes` TEXT not null,
  `default_area_m2` DECIMAL(10,2) not null default ('20'),
  `is_active` tinyint(1) not null default ('1'),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `photo_url` VARCHAR(255),
  `preview_image_path` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `tag_translations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tag_id` INT not null,
  `locale` VARCHAR(255) not null,
  `name` VARCHAR(255) not null,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tags` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) not null,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `name` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) not null,
  `email` VARCHAR(255) not null,
  `email_verified_at` DATETIME,
  `password` VARCHAR(255) not null,
  `remember_token` VARCHAR(255),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  `mapscalculator_role` VARCHAR(255) check (`mapscalculator_role` in ('admin', 'comercial', 'tecnico')) not null default 'comercial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Índices ----------
CREATE UNIQUE INDEX `accounting_accounts_code_unique` ON `accounting_accounts` (`code`);

CREATE UNIQUE INDEX `api_tokens_token_unique` ON `api_tokens` (`token`);

CREATE INDEX `api_tokens_tokenable_type_tokenable_id_index` ON `api_tokens` (`tokenable_type`, `tokenable_id`);

CREATE INDEX `audit_logs_created_at_index` ON `audit_logs` (`created_at`);

CREATE INDEX `audit_logs_event_index` ON `audit_logs` (`event`);

CREATE INDEX `audit_logs_subject_created_index` ON `audit_logs` (`subject_type`, `subject_id`, `created_at`);

CREATE INDEX `audit_logs_subject_id_index` ON `audit_logs` (`subject_id`);

CREATE INDEX `audit_logs_subject_type_index` ON `audit_logs` (`subject_type`);

CREATE UNIQUE INDEX `blog_post_translations_blog_post_id_locale_unique` ON `blog_post_translations` (`blog_post_id`, `locale`);

CREATE INDEX `blog_post_translations_locale_index` ON `blog_post_translations` (`locale`);

CREATE UNIQUE INDEX `blog_posts_slug_unique` ON `blog_posts` (`slug`);

CREATE UNIQUE INDEX `categories_slug_unique` ON `categories` (`slug`);

CREATE UNIQUE INDEX `category_translations_category_id_locale_unique` ON `category_translations` (`category_id`, `locale`);

CREATE INDEX `category_translations_locale_index` ON `category_translations` (`locale`);

CREATE UNIQUE INDEX `color_translations_color_id_locale_unique` ON `color_translations` (`color_id`, `locale`);

CREATE INDEX `color_translations_locale_index` ON `color_translations` (`locale`);

CREATE UNIQUE INDEX `colors_slug_unique` ON `colors` (`slug`);

CREATE INDEX `content_blocks_page_section_index` ON `content_blocks` (`page`, `section`);

CREATE UNIQUE INDEX `content_blocks_page_section_key_locale_unique` ON `content_blocks` (`page`, `section`, `key`, `locale`);

CREATE UNIQUE INDEX `customers_email_unique` ON `customers` (`email`);

CREATE UNIQUE INDEX `failed_jobs_uuid_unique` ON `failed_jobs` (`uuid`);

CREATE UNIQUE INDEX `inventory_items_sku_unique` ON `inventory_items` (`sku`);

CREATE UNIQUE INDEX `inventory_locations_code_unique` ON `inventory_locations` (`code`);

CREATE UNIQUE INDEX `inventory_locations_name_unique` ON `inventory_locations` (`name`);

CREATE INDEX `inventory_movements_item_location_created_index` ON `inventory_movements` (`inventory_item_id`, `inventory_location_id`, `created_at`);

CREATE INDEX `inventory_movements_work_order_created_index` ON `inventory_movements` (`work_order_id`, `created_at`);

CREATE INDEX `inventory_purchase_order_items_purchase_order_id_inventory_item_id_index` ON `inventory_purchase_order_items` (`purchase_order_id`, `inventory_item_id`);

CREATE UNIQUE INDEX `inventory_purchase_orders_number_unique` ON `inventory_purchase_orders` (`number`);

CREATE INDEX `inventory_purchase_orders_status_index` ON `inventory_purchase_orders` (`status`);

CREATE UNIQUE INDEX `inventory_receipt_items_inventory_movement_id_unique` ON `inventory_receipt_items` (`inventory_movement_id`);

CREATE INDEX `inventory_reservations_stock_index` ON `inventory_reservations` (`inventory_item_id`, `inventory_location_id`, `status`);

CREATE INDEX `inventory_reservations_work_order_index` ON `inventory_reservations` (`work_order_id`, `status`);

CREATE UNIQUE INDEX `inventory_valuation_item_location_unique` ON `inventory_valuation_balances` (`inventory_item_id`, `inventory_location_id`);

CREATE UNIQUE INDEX `invoices_invoice_number_unique` ON `invoices` (`invoice_number`);

CREATE UNIQUE INDEX `invoices_quote_id_unique` ON `invoices` (`quote_id`);

CREATE INDEX `jobs_queue_index` ON `jobs` (`queue`);

CREATE INDEX `journal_entries_entry_date_index` ON `journal_entries` (`entry_date`);

CREATE UNIQUE INDEX `journal_entries_entry_number_unique` ON `journal_entries` (`entry_number`);

CREATE UNIQUE INDEX `journal_entries_reversal_of_id_unique` ON `journal_entries` (`reversal_of_id`);

CREATE UNIQUE INDEX `journal_entries_reversed_by_entry_id_unique` ON `journal_entries` (`reversed_by_entry_id`);

CREATE INDEX `journal_entries_source_index` ON `journal_entries` (`source_type`, `source_id`);

CREATE INDEX `journal_entries_status_index` ON `journal_entries` (`status`);

CREATE INDEX `journal_lines_account_entry_index` ON `journal_entry_lines` (`accounting_account_id`, `journal_entry_id`);

CREATE INDEX `mapscalculator_clients_name_index` ON `mapscalculator_clients` (`name`);

CREATE UNIQUE INDEX `mapscalculator_clients_portal_token_unique` ON `mapscalculator_clients` (`portal_token`);

CREATE INDEX `mapscalculator_drawing_layers_measurement_id_sort_order_index` ON `mapscalculator_drawing_layers` (`measurement_id`, `sort_order`);

CREATE INDEX `mapscalculator_measurement_templates_user_id_type_index` ON `mapscalculator_measurement_templates` (`user_id`, `type`);

CREATE UNIQUE INDEX `mapscalculator_measurements_cloud_id_unique` ON `mapscalculator_measurements` (`cloud_id`);

CREATE INDEX `mapscalculator_price_items_category_index` ON `mapscalculator_price_items` (`category`);

CREATE UNIQUE INDEX `mapscalculator_price_items_slug_unique` ON `mapscalculator_price_items` (`slug`);

CREATE UNIQUE INDEX `mapscalculator_projects_share_token_unique` ON `mapscalculator_projects` (`share_token`);

CREATE INDEX `mapscalculator_projects_status_index` ON `mapscalculator_projects` (`status`);

CREATE INDEX `mapscalculator_subscriptions_provider_provider_subscription_id_index` ON `mapscalculator_subscriptions` (`provider`, `provider_subscription_id`);

CREATE INDEX `mapscalculator_subscriptions_user_id_status_index` ON `mapscalculator_subscriptions` (`user_id`, `status`);

CREATE INDEX `mapscalculator_sync_logs_syncable_index` ON `mapscalculator_sync_logs` (`syncable_type`, `syncable_id`);

CREATE INDEX `mapscalculator_sync_logs_syncable_type_syncable_id_index` ON `mapscalculator_sync_logs` (`syncable_type`, `syncable_id`);

CREATE INDEX `mapscalculator_sync_logs_user_status_index` ON `mapscalculator_sync_logs` (`user_id`, `status`);

CREATE UNIQUE INDEX `mapscalculator_work_logs_client_uuid_unique` ON `mapscalculator_work_logs` (`client_uuid`);

CREATE INDEX `mapscalculator_work_logs_date_index` ON `mapscalculator_work_logs` (`date`);

CREATE INDEX `mapscalculator_work_orders_status_index` ON `mapscalculator_work_orders` (`status`);

CREATE INDEX `media_model_type_model_id_index` ON `media` (`model_type`, `model_id`);

CREATE INDEX `media_order_column_index` ON `media` (`order_column`);

CREATE UNIQUE INDEX `media_uuid_unique` ON `media` (`uuid`);

CREATE INDEX `model_has_permissions_model_type_model_id_index` ON `model_has_permissions` (`model_type`, `model_id`);

CREATE INDEX `model_has_roles_model_type_model_id_index` ON `model_has_roles` (`model_type`, `model_id`);

CREATE UNIQUE INDEX `orders_order_number_unique` ON `orders` (`order_number`);

CREATE UNIQUE INDEX `orders_stripe_refund_id_unique` ON `orders` (`stripe_refund_id`);

CREATE INDEX `page_translations_locale_index` ON `page_translations` (`locale`);

CREATE UNIQUE INDEX `page_translations_page_id_locale_unique` ON `page_translations` (`page_id`, `locale`);

CREATE UNIQUE INDEX `pages_slug_unique` ON `pages` (`slug`);

CREATE UNIQUE INDEX `permissions_name_guard_name_unique` ON `permissions` (`name`, `guard_name`);

CREATE UNIQUE INDEX `personal_access_tokens_token_unique` ON `personal_access_tokens` (`token`);

CREATE INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` ON `personal_access_tokens` (`tokenable_type`, `tokenable_id`);

CREATE INDEX `product_translations_locale_index` ON `product_translations` (`locale`);

CREATE UNIQUE INDEX `product_translations_product_id_locale_unique` ON `product_translations` (`product_id`, `locale`);

CREATE INDEX `product_variant_translations_locale_index` ON `product_variant_translations` (`locale`);

CREATE UNIQUE INDEX `product_variant_translations_product_variant_id_locale_unique` ON `product_variant_translations` (`product_variant_id`, `locale`);

CREATE UNIQUE INDEX `product_variants_sku_unique` ON `product_variants` (`sku`);

CREATE UNIQUE INDEX `products_sku_unique` ON `products` (`sku`);

CREATE UNIQUE INDEX `products_slug_unique` ON `products` (`slug`);

CREATE INDEX `project_translations_locale_index` ON `project_translations` (`locale`);

CREATE UNIQUE INDEX `project_translations_project_id_locale_unique` ON `project_translations` (`project_id`, `locale`);

CREATE INDEX `projects_category_index` ON `projects` (`category`);

CREATE UNIQUE INDEX `projects_slug_unique` ON `projects` (`slug`);

CREATE UNIQUE INDEX `quote_payment_refunds_idempotency_key_unique` ON `quote_payment_refunds` (`idempotency_key`);

CREATE INDEX `quote_payment_refunds_status_index` ON `quote_payment_refunds` (`status`);

CREATE UNIQUE INDEX `quote_payment_refunds_stripe_refund_id_unique` ON `quote_payment_refunds` (`stripe_refund_id`);

CREATE UNIQUE INDEX `quote_payments_checkout_idempotency_key_unique` ON `quote_payments` (`checkout_idempotency_key`);

CREATE INDEX `quote_payments_status_index` ON `quote_payments` (`status`);

CREATE UNIQUE INDEX `quote_payments_stripe_checkout_session_id_unique` ON `quote_payments` (`stripe_checkout_session_id`);

CREATE INDEX `quote_payments_stripe_payment_intent_id_index` ON `quote_payments` (`stripe_payment_intent_id`);

CREATE INDEX `quotes_parent_quote_id_index` ON `quotes` (`parent_quote_id`);

CREATE INDEX `quotes_project_id_index` ON `quotes` (`project_id`);

CREATE INDEX `quotes_quote_number_index` ON `quotes` (`quote_number`);

CREATE UNIQUE INDEX `roles_name_guard_name_unique` ON `roles` (`name`, `guard_name`);

CREATE INDEX `sessions_last_activity_index` ON `sessions` (`last_activity`);

CREATE INDEX `sessions_user_id_index` ON `sessions` (`user_id`);

CREATE UNIQUE INDEX `settings_key_unique` ON `settings` (`key`);

CREATE UNIQUE INDEX `simulator_room_templates_slug_unique` ON `simulator_room_templates` (`slug`);

CREATE INDEX `tag_translations_locale_index` ON `tag_translations` (`locale`);

CREATE UNIQUE INDEX `tag_translations_tag_id_locale_unique` ON `tag_translations` (`tag_id`, `locale`);

CREATE UNIQUE INDEX `tags_slug_unique` ON `tags` (`slug`);

CREATE UNIQUE INDEX `users_email_unique` ON `users` (`email`);

SET FOREIGN_KEY_CHECKS=1;
