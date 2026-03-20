<?php
/**
 * Price Calendar Utility
 *
 * CLI utility to manage season rates, calendar templates, movable holidays
 * and to populate the price_calendar table.
 *
 * Usage examples:
 *   php price_calendar_util.php set-rate --season=HS --low=80.00 --standard=100.00 --high=120.00 --extra=15.00
 *   php price_calendar_util.php set-template --date-code=12-24 --description="Christmas Eve"
 *   php price_calendar_util.php add-holiday --date=2025-12-25 --name="Christmas Day"
 *   php price_calendar_util.php apply --start=2025-05-18 --end=2030-12-31
 */

class PriceCalendarUtil {
    /** @var PDO */
    protected $pdo;

    public function __construct(PDO \$pdo) {
        \$this->pdo = \$pdo;
        // Set PDO to throw exceptions
        \$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Create or update a season rate entry.
     */
    public function setSeasonPlanRate(string \$seasonCode, float \$low, float \$standard, float \$high, float \$extra): void {
        \$sql = "
            INSERT INTO season_plan_rates
              (season_code, low_rate, standard_rate, high_rate, extra_guest_fee)
            VALUES
              (:season, :low, :std, :high, :extra)
            ON DUPLICATE KEY UPDATE
              low_rate = VALUES(low_rate),
              standard_rate = VALUES(standard_rate),
              high_rate = VALUES(high_rate),
              extra_guest_fee = VALUES(extra_guest_fee)
        ";
        \$stmt = \$this->pdo->prepare(\$sql);
        \$stmt->execute([
            ':season' => \$seasonCode,
            ':low'    => \$low,
            ':std'    => \$standard,
            ':high'   => \$high,
            ':extra'  => \$extra
        ]);
        echo "Season plan rate for {\$seasonCode} set.\n";
    }

    /**
     * Create or update a template entry (recurring season calendar).
     * dateCode in 'MM-DD' format.
     */
    public function setSeasonCalendarTemplate(string \$dateCode, string \$description): void {
        \$sql = "
            INSERT INTO season_calendar_template
              (date_code, description)
            VALUES
              (:code, :desc)
            ON DUPLICATE KEY UPDATE
              description = VALUES(description)
        ";
        \$stmt = \$this->pdo->prepare(\$sql);
        \$stmt->execute([
            ':code' => \$dateCode,
            ':desc' => \$description
        ]);
        echo "Template for date code {\$dateCode} set.\n";
    }

    /**
     * Add or update a movable holiday by exact date.
     */
    public function addMovableHoliday(string \$date, string \$name): void {
        \$sql = "
            INSERT INTO movable_holidays
              (holiday_date, holiday_name)
            VALUES
              (:date, :name)
            ON DUPLICATE KEY UPDATE
              holiday_name = VALUES(holiday_name)
        ";
        \$stmt = \$this->pdo->prepare(\$sql);
        \$stmt->execute([
            ':date' => \$date,
            ':name' => \$name
        ]);
        echo "Movable holiday on {\$date} named '{\$name}' set.\n";
    }

    /**
     * Populate the price_calendar table by calling the stored procedure.
     */
    public function applyCalendar(string \$start, string \$end): void {
        \$stmt = \$this->pdo->prepare('CALL fill_price_calendar(:start, :end)');
        \$stmt->execute([':start' => \$start, ':end' => \$end]);
        echo "Price calendar filled from {\$start} to {\$end}.\n";
    }
}

// --------- CLI Dispatch ---------
$options = getopt('', [
    'season:', 'low:', 'standard:', 'high:', 'extra:',
    'date-code:', 'description:',
    'date:', 'name:',
    'start:', 'end:'
]);
$args = array_slice(\$argv, 1);
if (empty(\$args)) {
    echo "No command specified. Available: set-rate, set-template, add-holiday, apply\n";
    exit(1);
}

// Initialize PDO
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=myscalea;charset=utf8mb4', 'root', '');
} catch (PDOException \$e) {
    echo "DB Connection failed: " . \$e->getMessage() . "\n";
    exit(1);
}

\$util = new PriceCalendarUtil(\$pdo);
\$command = \$args[0];
switch (\$command) {
    case 'set-rate':
        \$util->setSeasonPlanRate(\$options['season'], \$options['low'], \$options['standard'], \$options['high'], \$options['extra']);
        break;
    case 'set-template':
        \$util->setSeasonCalendarTemplate(\$options['date-code'], \$options['description']);
        break;
    case 'add-holiday':
        \$util->addMovableHoliday(\$options['date'], \$options['name']);
        break;
    case 'apply':
        \$util->applyCalendar(\$options['start'], \$options['end']);
        break;
    default:
        echo "Unknown command '{\$command}'.\n";
        exit(1);
}

