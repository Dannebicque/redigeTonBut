\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{PN-BUT}[2021/09/10 trame du Programme National des B.U.T.]

%%%  Chargement de la classe article, avec transfert d'options
\PassOptionsToClass{a4paper,10pt,twoside,openright,titlepage}{article} % format a4paper par défaut
\DeclareOption*{\PassOptionsToClass{\CurrentOption}{book}}
\ProcessOptions

\LoadClass{book}

\RequirePackage[utf8]{inputenc}
\RequirePackage[cyr]{aeguill}
\RequirePackage[french]{babel}
%%%  Chargement des packages les plus courants
\RequirePackage[dvips]{epsfig}
\RequirePackage{fancyhdr}
\RequirePackage{pdfpages}
\RequirePackage{geometry}
\RequirePackage{url}

%% choix de la fonte de caractères
\usepackage{helvet}
\renewcommand{\familydefault}{\sfdefault}




%%%% marges du document
%\setlength{\hoffset}{-18pt}
%\setlength{\oddsidemargin}{0pt} % Marge gauche sur pages impaires
%\setlength{\evensidemargin}{0pt} % Marge gauche sur pages paires
%\setlength{\marginparwidth}{54pt} % Largeur de note dans la marge
%\setlength{\textwidth}{537pt} % Largeur de la zone de texte (17cm)
%\setlength{\voffset}{-18pt} % Bon pour DOS
%\setlength{\marginparsep}{7pt} % Séparation de la marge
%\setlength{\topmargin}{10pt} % Pas de marge en haut
%\setlength{\headheight}{13pt} % Haut de page
%\setlength{\headsep}{10pt} % Entre le haut de page et le texte
%\setlength{\footskip}{57pt} % Bas de page + séparation
%\setlength{\textheight}{656pt} % Hauteur de la zone de texte (25cm)

\geometry{hmargin=1cm,vmargin=2cm}

%% entetes et pieds de pages
\RequirePackage{fancyhdr}

%% numérotation des sections
\setcounter{secnumdepth}{3}
\setcounter{tocdepth}{3}
\renewcommand{\thesection}{\arabic{section}}
\renewcommand{\thesubsection}{\arabic{section}.\arabic{subsection}}
\renewcommand{\thesubsubsection}{\arabic{section}.\arabic{subsection}.\arabic{subsubsection}}


%% espace entre les paragraphes
\parskip=10pt

%% page vide après la page de garde
\usepackage{afterpage}
\newcommand\mapagevide{
\null
\thispagestyle{empty}
\addtocounter{page}{0}
\newpage
}

\RequirePackage{minitoc}

\def\PageDeGrade#1{\ifx}
%%%  Destination de l'article (proposé pour une conf, une revue...)

\def\Destination#1{\ifx{#1}{}\gdef\@Destination{}%
\else\gdef\@Destination{#1}\fi}

%%%   Destination vide par défaut

\Destination{}

%%%   Auteur par défaut, pour eviter de recopier a chaque fois

\author{Vincent \fsc{Henn}\thanks{Laboratoire d'ingénierie
circulation--transport, (Unité mixte \lsc{INRETS}--\lsc{ENTPE}),
109, Avenue Salvador Allende, Case 24,
F--69675 \fsc{Bron} Cedex, {\tt henn@inrets.fr}.}}

