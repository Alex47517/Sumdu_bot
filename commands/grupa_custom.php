<?php
//
// Command: Група #
// Text: !группа !група /group #
// Info: Встановлює групу для профілю #
// Syntax: !група [назва групи] #
// Args: 1 #
// Rank: USER #
//
$grp = R::findOne("groups", "grp = ?", [$cmd[1]]);
if ($grp) {
$user->update("grp", $grp["grp"]);
$chat->sendMessage("✅ Група <b>".$grp["grp"]."</b> встановлена для вашого профілю");
} else {
custom_error("Група не знайдена", "Спробуйте зареєструватися через студ. портал");
}