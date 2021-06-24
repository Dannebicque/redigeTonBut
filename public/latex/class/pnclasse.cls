\NeedsTeXFormat{LaTeX2e}

\ProvidesClass{pnclasse}[2021/06/22 Classe personnelle, V1.0]

% classe de base

\LoadClass[a4paper, 11pt]{book}

% extensions

\RequirePackage[utf8]{inputenc}
\RequirePackage[T1]{fontenc}
%\RequirePackage{fontspec}
%\setmainfont{arial}
\RequirePackage[frenchb]{babel}

% commandes personnelles

\newcommand{\langue}{\emph} % mots en langues étrangères
\newcommand{\citital}{\emph} % citation en italique
\newcommand{\nomprog}{\texttt} % nom de programme en police teletype
