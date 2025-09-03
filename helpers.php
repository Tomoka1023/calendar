<?php
function ym_from_query(): string {
$ym = $_GET['ym'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
return $ym;
}
function month_dates(string $ym): array {
$first = new DateTime("$ym-01");
$daysInMonth = (int)$first->format('t');
$dates = [];
for ($d = 1; $d <= $daysInMonth; $d++) $dates[] = new DateTime("$ym-" . str_pad($d, 2, '0', STR_PAD_LEFT));
return $dates;
}
function prev_next_links(string $ym): array {
$dt = new DateTime("$ym-01");
return [
(clone $dt)->modify('-1 month')->format('Y-m'),
(clone $dt)->modify('+1 month')->format('Y-m')
];
}
function is_same_day(DateTime $a, DateTime $b): bool {
return $a->format('Y-m-d') === $b->format('Y-m-d');
}