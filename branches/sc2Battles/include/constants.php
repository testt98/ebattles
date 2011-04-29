<?php
/**
 * Constants.php
 *
 * This file is intended to group all constants to
 * make it easier for the site administrator to tweak
 * the login script.
 *
 */

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_PREFIX", "ebattles_");

define("TBL_USERS_SHORT",           "user");
define("TBL_LADDERS_SHORT",         TBL_PREFIX."Ladders");
define("TBL_MODS_SHORT",            TBL_PREFIX."Moderators");
define("TBL_TEAMS_SHORT",           TBL_PREFIX."Teams");
define("TBL_MATCHS_SHORT",          TBL_PREFIX."Matchs");
define("TBL_PLAYERS_SHORT",         TBL_PREFIX."Players");
define("TBL_SCORES_SHORT",          TBL_PREFIX."Scores");
define("TBL_CLANS_SHORT",           TBL_PREFIX."Clans");
define("TBL_DIVISIONS_SHORT",       TBL_PREFIX."Divisions");
define("TBL_MEMBERS_SHORT",         TBL_PREFIX."Members");
define("TBL_STATSCATEGORIES_SHORT", TBL_PREFIX."StatsCategories");
define("TBL_GAMES_SHORT",           TBL_PREFIX."Games");
define("TBL_AWARDS_SHORT",          TBL_PREFIX."Awards");
define("TBL_MAPS_SHORT",            TBL_PREFIX."Maps");
define("TBL_FACTIONS_SHORT",        TBL_PREFIX."Factions");
define("TBL_MEDIA_SHORT",           TBL_PREFIX."Media");
define("TBL_CHALLENGES_SHORT",      TBL_PREFIX."Challenges");
define("TBL_GAMERS_SHORT",          TBL_PREFIX."Gamers");
define("TBL_OFFICIAL_LADDERS_SHORT",TBL_PREFIX."OfficialLadders");
define("TBL_TOURNAMENTS_SHORT",     TBL_PREFIX."Tournaments");
define("TBL_ROUNDS_SHORT",          TBL_PREFIX."Rounds");
define("TBL_TPLAYERS_SHORT",        TBL_PREFIX."TournamentPlayers");
define("TBL_TTEAMS_SHORT",          TBL_PREFIX."TournamentTeams");

define("TBL_USERS",           MPREFIX."user");
define("TBL_LADDERS",         MPREFIX.TBL_LADDERS_SHORT);
define("TBL_MODS",            MPREFIX.TBL_MODS_SHORT);
define("TBL_TEAMS",           MPREFIX.TBL_TEAMS_SHORT);
define("TBL_MATCHS",          MPREFIX.TBL_MATCHS_SHORT);
define("TBL_PLAYERS",         MPREFIX.TBL_PLAYERS_SHORT);
define("TBL_SCORES",          MPREFIX.TBL_SCORES_SHORT);
define("TBL_CLANS",           MPREFIX.TBL_CLANS_SHORT);
define("TBL_DIVISIONS",       MPREFIX.TBL_DIVISIONS_SHORT);
define("TBL_MEMBERS",         MPREFIX.TBL_MEMBERS_SHORT);
define("TBL_STATSCATEGORIES", MPREFIX.TBL_STATSCATEGORIES_SHORT);
define("TBL_GAMES",           MPREFIX.TBL_GAMES_SHORT);
define("TBL_AWARDS",          MPREFIX.TBL_AWARDS_SHORT);
define("TBL_MAPS",            MPREFIX.TBL_MAPS_SHORT);
define("TBL_FACTIONS",        MPREFIX.TBL_FACTIONS_SHORT);
define("TBL_MEDIA",           MPREFIX.TBL_MEDIA_SHORT);
define("TBL_CHALLENGES",      MPREFIX.TBL_CHALLENGES_SHORT);
define("TBL_GAMERS",          MPREFIX.TBL_GAMERS_SHORT);
define("TBL_OFFICIAL_LADDERS",MPREFIX.TBL_OFFICIAL_LADDERS_SHORT);
define("TBL_TOURNAMENTS",     MPREFIX.TBL_TOURNAMENTS_SHORT);
define("TBL_ROUNDS",          MPREFIX.TBL_ROUNDS_SHORT);
define("TBL_TPLAYERS",        MPREFIX.TBL_TPLAYERS_SHORT);
define("TBL_TTEAMS",          MPREFIX.TBL_TTEAMS_SHORT);

/**
 * Email Constants - these specify what goes in
 * the from field in the emails that the script
 * sends to users, and whether to send a
 * welcome email to newly registered users.
 */
define("EMAIL_FROM_NAME", "eBattles");
define("EMAIL_FROM_ADDR", "frederic.marchais@gmail.com");
define("EMAIL_PASSWORD", "gmax76");
define("EMAIL_WELCOME", true);

define("ELO_DEFAULT", 1000);
define("ELO_K", 50);
define("ELO_M", 100);
define("TS_Mu0"     , 25);
define("TS_sigma0"  , TS_Mu0/3);
define("TS_beta"    , TS_Mu0/6);
define("TS_epsilon" , 1.0);
define("PointsPerWin_DEFAULT" , 3);
define("PointsPerDraw_DEFAULT" , 1);
define("PointsPerLoss_DEFAULT" , 0);

// Match report userclass
define("eb_UC_EB_MODERATOR", 8);
define("eb_UC_LADDER_OWNER", 4);
define("eb_UC_LADDER_MODERATOR", 2);
define("eb_UC_LADDER_PLAYER", 1);
define("eb_UC_NONE", 0);

define("eb_PAGINATION_MIDRANGE", 7);

define("eb_MATCH_NOLADDERINFO", 1);
define("eb_MATCH_SCHEDULED", 2);

define("eb_MAX_CHALLENGE_DATES", 3);
define("eb_MAX_MAPS_PER_MATCH", 1);
?>