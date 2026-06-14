<?php
// CRK 2024 CBT - Standalone PHP Page
// This file handles both loading and serving the CBT

// --- EMBEDDED JSON DATA (Government 2023 Data) ---
// Note: The template expects a 'sections' array, so the flat data is wrapped accordingly.
$jsonContent = '{
  "sections": [
    {
      "sectionId": 1,
      "sectionName": "Government",
      "questions": [
        {
          "questionId": 1,
          "question": "Apart from globalization, what other factor to a large extent affects the sovereignty of a state?",
          "options": [
            { "optionId": "A", "text": "Right of a franchise" },
            { "optionId": "B", "text": "System of government" },
            { "optionId": "C", "text": "International laws" },
            { "optionId": "D", "text": "Inalienability" }
          ],
          "correctAnswer": "C",
          "explanation": "International laws and treaties, which states voluntarily sign, often require them to adhere to certain rules (e.g., trade, human rights, environmental) that can limit their absolute or independent authority (sovereignty)."
        },
        {
          "questionId": 2,
          "question": "A public declaration of policy and aims especially one issued before an election by a political party or candidate is commonly called",
          "options": [
            { "optionId": "A", "text": "steps to govern" },
            { "optionId": "B", "text": "Policy paper" },
            { "optionId": "C", "text": "guide to government" },
            { "optionId": "D", "text": "manifesto" }
          ],
          "correctAnswer": "D",
          "explanation": "This is the standard definition of a political manifesto, which outlines a party\'s intentions and promises to the electorate."
        },
        {
          "questionId": 3,
          "question": "The media, commonly referred to as the fourth estate of the realm , monitors the activities of government on behalf of the",
          "options": [
            { "optionId": "A", "text": "people" },
            { "optionId": "B", "text": "executive" },
            { "optionId": "C", "text": "civil society" },
            { "optionId": "D", "text": "media organisation" }
          ],
          "correctAnswer": "A",
          "explanation": "The concept of the \'fourth estate\' positions the press as a watchdog that holds the government accountable to the public (the people)."
        },
        {
          "questionId": 4,
          "question": "Bicameral legislature contributes to the democratic process by",
          "options": [
            { "optionId": "A", "text": "recommending the use of force on policy" },
            { "optionId": "B", "text": "suppressing minority rights" },
            { "optionId": "C", "text": "imposing programmes and policies on the executive" },
            { "optionId": "D", "text": "widening the scope of political participation" }
          ],
          "correctAnswer": "D",
          "explanation": "A bicameral (two-house) legislature, often with different representation bases (e.g., population and region), allows more diverse interests and viewpoints to be represented, thereby widening political participation."
        },
        {
          "questionId": 5,
          "question": "Maintenance of law and order in a state is the function of the",
          "options": [
            { "optionId": "A", "text": "citizens of the state" },
            { "optionId": "B", "text": "executive arm of government" },
            { "optionId": "C", "text": "legislative arm of government" },
            { "optionId": "D", "text": "judicial service" }
          ],
          "correctAnswer": "B",
          "explanation": "The executive arm is responsible for implementing and enforcing laws. This includes the police force and other security agencies tasked with maintaining public order."
        },
        {
          "questionId": 6,
          "question": "The official routine observed in carrying out functions in the civil service is commonly known as",
          "options": [
            { "optionId": "A", "text": "official order" },
            { "optionId": "B", "text": "bureaucracy" },
            { "optionId": "C", "text": "standard order" },
            { "optionId": "D", "text": "command" }
          ],
          "correctAnswer": "B",
          "explanation": "Bureaucracy is the administrative system governing any large institution, characterized by set procedures, hierarchy, and official routines."
        },
        {
          "questionId": 7,
          "question": "The Economic Community of West African State (ECOWAS) is now confronted with a new security threat which is",
          "options": [
            { "optionId": "A", "text": "extremism" },
            { "optionId": "B", "text": "smuggling of illegal goods across states" },
            { "optionId": "C", "text": "weak national armies" },
            { "optionId": "D", "text": "proportional representation" }
          ],
          "correctAnswer": "A",
          "explanation": "In recent years, violent extremism and terrorism (e.g., from groups in the Sahel, Boko Haram) have become the most pressing new security challenges for the ECOWAS region."
        },
        {
          "questionId": 8,
          "question": "The art of maintaining peaceful relationships between states, groups or individuals mostly comes about as a result of the activities and functions of",
          "options": [
            { "optionId": "A", "text": "the legislature" },
            { "optionId": "B", "text": "security agencies" },
            { "optionId": "C", "text": "diplomats" },
            { "optionId": "D", "text": "the media" }
          ],
          "correctAnswer": "C",
          "explanation": "Diplomacy is the art and practice of conducting negotiations and maintaining relations between states. This is the primary role of diplomats."
        },
        {
          "questionId": 9,
          "question": "The following issues are some of the challenges the United Nations (UN) addresses except",
          "options": [
            { "optionId": "A", "text": "oligarchical regimes" },
            { "optionId": "B", "text": "humanitarian aids" },
            { "optionId": "C", "text": "human rights" },
            { "optionId": "D", "text": "climate change" }
          ],
          "correctAnswer": "A",
          "explanation": "The UN provides humanitarian aid, promotes human rights, and addresses climate change. While it promotes democracy, it generally does not intervene in a state\'s internal form of government (like an oligarchy) due to principles of state sovereignty, unless that regime is a threat to international peace or committing mass human rights violations."
        },
        {
          "questionId": 10,
          "question": "Which system of government allows for and guarantees maximum competition?",
          "options": [
            { "optionId": "A", "text": "Feudalism" },
            { "optionId": "B", "text": "Capitalism" },
            { "optionId": "C", "text": "Communism" },
            { "optionId": "D", "text": "Social Democracy" }
          ],
          "correctAnswer": "B",
          "explanation": "Capitalism is an economic system based on private ownership and free markets, where competition is a core principle. Communism, by contrast, involves state control and the elimination of market competition."
        },
        {
          "questionId": 11,
          "question": "The overriding consideration for the creation of the New Partnership for Africa\'s Development (NEPAD) was to place Africa on the path to",
          "options": [
            { "optionId": "A", "text": "capitalist government" },
            { "optionId": "B", "text": "non-alignment" },
            { "optionId": "C", "text": "growth and development" },
            { "optionId": "D", "text": "military might" }
          ],
          "correctAnswer": "C",
          "explanation": "NEPAD is the economic development program of the African Union (AU), designed to promote sustainable growth, eradicate poverty, and foster development across the continent."
        },
        {
          "questionId": 12,
          "question": "The French Colonial policies in her West African colonies were different from the British in many ways except in",
          "options": [
            { "optionId": "A", "text": "discriminatory laws" },
            { "optionId": "B", "text": "citizenship" },
            { "optionId": "C", "text": "imperialism" },
            { "optionId": "D", "text": "cultural imposition" }
          ],
          "correctAnswer": "C",
          "explanation": "Both the French (using assimilation/direct rule) and the British (using indirect rule) were imperialist powers. Imperialism (the policy of extending a country\'s power) was the one thing they had in common, even if their methods (A, B, D) differed."
        },
        {
          "questionId": 13,
          "question": "The reason for which the government should take a key interest in the activities of pressure groups is that, they",
          "options": [
            { "optionId": "A", "text": "provide valuable feedback on government policies and programmes" },
            { "optionId": "B", "text": "force coalition among political parties for election" },
            { "optionId": "C", "text": "blackmail political leaders" },
            { "optionId": "D", "text": "dictate the path and direction for government programmes" }
          ],
          "correctAnswer": "A",
          "explanation": "Pressure groups articulate the specific interests of parts of society, giving the government important feedback on how its policies are affecting different groups."
        },
        {
          "questionId": 14,
          "question": "The rigid nature of a constitution makes it",
          "options": [
            { "optionId": "A", "text": "accessible to all" },
            { "optionId": "B", "text": "infallible and standard" },
            { "optionId": "C", "text": "easy for quick review" },
            { "optionId": "D", "text": "complicated for amendment" }
          ],
          "correctAnswer": "D",
          "explanation": "A \'rigid\' constitution is defined by having a difficult or special procedure for amendment, making it complicated to change, unlike a \'flexible\' constitution."
        },
        {
          "questionId": 15,
          "question": "The Fundamental Human Rights and Liberties must be entrenched in a constitution to",
          "options": [
            { "optionId": "A", "text": "serve majority interest" },
            { "optionId": "B", "text": "show concern for the people" },
            { "optionId": "C", "text": "make it difficult to tamper with" },
            { "optionId": "D", "text": "espouse love for the country" }
          ],
          "correctAnswer": "C",
          "explanation": "Entrenching rights in a constitution places them above ordinary laws, meaning they cannot be easily changed or \'tampered with\' by a simple government majority."
        },
        {
          "questionId": 16,
          "question": "Unitary system of government is adopted by some states because",
          "options": [
            { "optionId": "A", "text": "of homogeneity" },
            { "optionId": "B", "text": "it guarnatees democracy" },
            { "optionId": "C", "text": "it builds the capacity of citizens in good time" },
            { "optionId": "D", "text": "of mineral resources" }
          ],
          "correctAnswer": "A",
          "explanation": "A unitary system (with a strong central government) is best suited for countries that are geographically small and/or have a homogeneous population (lacking deep ethnic or linguistic divides), as there\'s less demand for regional autonomy."
        },
        {
          "questionId": 17,
          "question": "The public Service Commission performs the following functions except",
          "options": [
            { "optionId": "A", "text": "advisory body" },
            { "optionId": "B", "text": "imposition of income tax" },
            { "optionId": "C", "text": "welfare of personnel" },
            { "optionId": "D", "text": "discipline of personnel" }
          ],
          "correctAnswer": "B",
          "explanation": "The Public Service Commission deals with personnel management (appointments, welfare, discipline). Imposing taxes is a fiscal policy function handled by the legislature and the Ministry of Finance."
        },
        {
          "questionId": 18,
          "question": "What type of right enables citizens to participate in state affairs?",
          "options": [
            { "optionId": "A", "text": "judicial right" },
            { "optionId": "B", "text": "social right" },
            { "optionId": "C", "text": "habeas corpus" },
            { "optionId": "D", "text": "political right" }
          ],
          "correctAnswer": "D",
          "explanation": "Political rights (like the right to vote, run for office, and join a party) are specifically those that allow citizens to participate in the political life and governance of the state."
        },
        {
          "questionId": 19,
          "question": "What is commonly referred to as the association of the government and the governed?",
          "options": [
            { "optionId": "A", "text": "confederation" },
            { "optionId": "B", "text": "state" },
            { "optionId": "C", "text": "federation" },
            { "optionId": "D", "text": "monarchy" }
          ],
          "correctAnswer": "B",
          "explanation": "A \'state\' is the political entity comprising a territory, a population (the governed), and a government that exercises sovereignty over them."
        },
        {
          "questionId": 20,
          "question": "The local government system assists best in mentoring and grooming people for",
          "options": [
            { "optionId": "A", "text": "military oprations" },
            { "optionId": "B", "text": "leadership positions" },
            { "optionId": "C", "text": "citizenship and justice" },
            { "optionId": "D", "text": "large-scale farming" }
          ],
          "correctAnswer": "B",
          "explanation": "Local government is often called a \'nursery for democracy\' as it provides a training ground for aspiring politicians and administrators to gain experience before moving to higher leadership positions."
        },
        {
          "questionId": 21,
          "question": "Which of the following systems of government does not practice collective responsibility?",
          "options": [
            { "optionId": "A", "text": "Communalism system" },
            { "optionId": "B", "text": "Non-centralised system" },
            { "optionId": "C", "text": "Executive system" },
            { "optionId": "D", "text": "Cabinet system" }
          ],
          "correctAnswer": "C",
          "explanation": "The \'Cabinet system\' (Parliamentary) is defined by collective responsibility. The \'Executive system\' (Presidential) is based on the separation of powers, where cabinet members are individually accountable to the President, not collectively to the legislature."
        },
        {
          "questionId": 22,
          "question": "One of the formidable challenges the New Partnership for Africa\'s Development (NEPAD) is confronted with is the",
          "options": [
            { "optionId": "A", "text": "absence of a common platform for debate" },
            { "optionId": "B", "text": "over reliance on foreign aid" },
            { "optionId": "C", "text": "absence of technology transfer" },
            { "optionId": "D", "text": "arms race among member states" }
          ],
          "correctAnswer": "B",
          "explanation": "A major and persistent challenge for NEPAD has been its heavy dependence on funding from external partners and donors (foreign aid) rather than internally generated African capital."
        },
        {
          "questionId": 23,
          "question": "The biggest obstacle which also slowed down nationalist activities in French West African Colonies was the",
          "options": [
            { "optionId": "A", "text": "assimilation policy" },
            { "optionId": "B", "text": "constitutional reforms and entrenchment of human rights" },
            { "optionId": "C", "text": "assassination of chiefs" },
            { "optionId": "D", "text": "deportation of Africans to France" }
          ],
          "correctAnswer": "A",
          "explanation": "The French policy of assimilation created a small, educated elite of \'French\' Africans. This co-opted many potential nationalist leaders, separating them from the masses and thus slowing the momentum of the independence movement."
        },
        {
          "questionId": 24,
          "question": "A state that has two levels of government is commonly referred to as",
          "options": [
            { "optionId": "A", "text": "concurrent states" },
            { "optionId": "B", "text": "cooperative government" },
            { "optionId": "C", "text": "federal state" },
            { "optionId": "D", "text": "coalition government" }
          ],
          "correctAnswer": "C",
          "explanation": "A federal state is defined by the constitutional division of sovereignty between two levels of government: a central (federal) government and regional (state or provincial) governments."
        },
        {
          "questionId": 25,
          "question": "One of the major criticisms levelled against the United Nations (UN) is its inability to",
          "options": [
            { "optionId": "A", "text": "ensure fair permanent representation" },
            { "optionId": "B", "text": "enforce affirmative action and equal wealth" },
            { "optionId": "C", "text": "adopt a universal language for its operation" },
            { "optionId": "D", "text": "ensure universal access to education" }
          ],
          "correctAnswer": "A",
          "explanation": "This criticism points to the UN Security Council, where the five permanent (P5) members reflect the global power balance of 1945, not the 21st century. Continents like Africa and South America lack permanent representation, which is seen as unfair."
        },
        {
          "questionId": 26,
          "question": "One of the function of the electoral management body is to",
          "options": [
            { "optionId": "A", "text": "conduct training programmes for presidential candidates" },
            { "optionId": "B", "text": "look for external sources besides the government to fund its operations" },
            { "optionId": "C", "text": "interpret the electoral laws fairly" },
            { "optionId": "D", "text": "engage in business to increase its financial capacity" }
          ],
          "correctAnswer": "C",
          "explanation": "The core mandate of an electoral management body (like an electoral commission) is to administer elections, which includes registering voters, conducting the poll, and applying/interpreting the electoral laws in an impartial manner."
        },
        {
          "questionId": 27,
          "question": "The civil service is different from the public service in terms of",
          "options": [
            { "optionId": "A", "text": "educational qualification" },
            { "optionId": "B", "text": "political affiliation" },
            { "optionId": "C", "text": "conditions of service" },
            { "optionId": "D", "text": "personality and stature" }
          ],
          "correctAnswer": "C",
          "explanation": "The \'public service\' is broad (includes teachers, police, military, judiciary, etc.). The \'civil service\' is a narrower part of the public service (core government ministries). They operate under different rules, pay scales, and employment terms, which are collectively known as \'conditions of service\'."
        },
        {
          "questionId": 28,
          "question": "The judiciary occupies an important place in every state because it is",
          "options": [
            { "optionId": "A", "text": "mandated to supervise the functions of the executive" },
            { "optionId": "B", "text": "a unique profession of intellectuals" },
            { "optionId": "C", "text": "protector of the rights of the people" },
            { "optionId": "D", "text": "a body of upright and just people in society" }
          ],
          "correctAnswer": "C",
          "explanation": "A primary and essential function of an independent judiciary is to interpret the constitution and laws, thereby serving as a check on government power and protecting the fundamental rights of citizens."
        },
        {
          "questionId": 29,
          "question": "An executive president is one who wields the powers of a",
          "options": [
            { "optionId": "A", "text": "head of state and government" },
            { "optionId": "B", "text": "ceremonial president only" },
            { "optionId": "C", "text": "leader of government business only" },
            { "optionId": "D", "text": "despot and benevolent dictator" }
          ],
          "correctAnswer": "A",
          "explanation": "In a presidential (executive) system, the president fulfills both the ceremonial duties of Head of State and the practical, day-to-day duties of the Head of Government."
        },
        {
          "questionId": 30,
          "question": "There are two types of committee system in the House of parliament as part of their operations and one of such is",
          "options": [
            { "optionId": "A", "text": "parliamentary service board" },
            { "optionId": "B", "text": "expert committee" },
            { "optionId": "C", "text": "Committee of the Whole House" },
            { "optionId": "D", "text": "Leadership Committee" }
          ],
          "correctAnswer": "C",
          "explanation": "Parliamentary committees are typically either Standing Committees (permanent) or Ad-hoc/Select Committees (temporary). A \'Committee of the Whole House\' is a specific procedural tool where the entire chamber acts as one committee, usually to debate a bill in detail."
        },
        {
          "questionId": 31,
          "question": "A democratic country is one in which the people have the right to",
          "options": [
            { "optionId": "A", "text": "participate in decision making" },
            { "optionId": "B", "text": "disregard laws they abhor" },
            { "optionId": "C", "text": "violently overthrow a government" },
            { "optionId": "D", "text": "punish government by not honouring tax obligation" }
          ],
          "correctAnswer": "A",
          "explanation": "The core principle of democracy (\'rule by the people\') is that citizens have the right to participate in the decisions that affect them, primarily through voting for representatives."
        },
        {
          "questionId": 32,
          "question": "The best way to keep the military at bay from interfering in politics is by ensuring",
          "options": [
            { "optionId": "A", "text": "high pay rise every year" },
            { "optionId": "B", "text": "their inclusion in government" },
            { "optionId": "C", "text": "good governance" },
            { "optionId": "D", "text": "effective control of their day to day operations" }
          ],
          "correctAnswer": "C",
          "explanation": "Military interventions (coups) are often justified by citing government corruption, incompetence, and instability. Therefore, providing good, effective, and accountable governance removes the primary pretext for military intervention and maintains popular support for civilian rule."
        },
        {
          "questionId": 33,
          "question": "The African Union (AU) is saddled with one of these challenges and that is",
          "options": [
            { "optionId": "A", "text": "absence of rich natural resources" },
            { "optionId": "B", "text": "tensions and political discord among member states" },
            { "optionId": "C", "text": "inter-ethnic extinction and poor social cohesion" },
            { "optionId": "D", "text": "weak labour force" }
          ],
          "correctAnswer": "B",
          "explanation": "As an organization of 55 sovereign states, the AU frequently faces challenges in reaching a consensus. Differing national interests, political conflicts, and regional disputes among member states create political discord that can hinder effective, unified action."
        },
        {
          "questionId": 34,
          "question": "The reluctance of military regimes to hand over power to civilian administration is because they",
          "options": [
            { "optionId": "A", "text": "negotiate better loan terms for the country than the civilian government" },
            { "optionId": "B", "text": "have the backing of traditional rulers, their subjects and the clergy" },
            { "optionId": "C", "text": "feel a sense of entitlement" },
            { "optionId": "D", "text": "perceive civilians and politicians as evil doers and backward" }
          ],
          "correctAnswer": "D",
          "explanation": "Military regimes often justify their rule with a \'messianic\' complex, claiming that civilian politicians are corrupt, inept, and divisive. This perception that they are \'saving\' the nation makes them reluctant to return power to those they deem unfit to rule."
        },
        {
          "questionId": 35,
          "question": "The exercise of power can arise from any of the following sources except",
          "options": [
            { "optionId": "A", "text": "ideological power" },
            { "optionId": "B", "text": "legitimate power" },
            { "optionId": "C", "text": "expert power" },
            { "optionId": "D", "text": "charismatic power" }
          ],
          "correctAnswer": "A",
          "explanation": "Based on French and Raven\'s \'Bases of Power,\' the common sources are Legitimate (position), Expert (knowledge), Referent (charismatic), Coercive (punishment), and Reward. \'Ideological power\' is not one of the distinct classical types, though it can be related to legitimate or charismatic power."
        },
        {
          "questionId": 36,
          "question": "A social, political and economic arrangement in which landed property and other natural resources are collectively owned is referred to as",
          "options": [
            { "optionId": "A", "text": "Federalism" },
            { "optionId": "B", "text": "Liberalism" },
            { "optionId": "C", "text": "Communalism" },
            { "optionId": "D", "text": "Feudalism" }
          ],
          "correctAnswer": "C",
          "explanation": "Communalism is a system of social organization based on the collective ownership of property, particularly land and resources, by the community as a whole."
        },
        {
          "questionId": 37,
          "question": "Public opinion is formed through the following channels except",
          "options": [
            { "optionId": "A", "text": "Cabinet" },
            { "optionId": "B", "text": "Elections" },
            { "optionId": "C", "text": "Mass media" },
            { "optionId": "D", "text": "Political parties" }
          ],
          "correctAnswer": "A",
          "explanation": "The mass media, political parties, and elections are all ways public opinion is formed, expressed, and measured. The Cabinet, however, is part of the government (executive) that *responds* to public opinion; it does not form it."
        },
        {
          "questionId": 38,
          "question": "The function of the Diplomatic Missions include the following except",
          "options": [
            { "optionId": "A", "text": "project the culture of the people" },
            { "optionId": "B", "text": "woo investors" },
            { "optionId": "C", "text": "pay salaries of workers of the mission" },
            { "optionId": "D", "text": "gather intelligence information" }
          ],
          "correctAnswer": "C",
          "explanation": "Paying salaries is an internal administrative task. The diplomatic functions are representing the state, promoting its culture, facilitating trade (wooing investors), and gathering information (intelligence) about the host country."
        },
        {
          "questionId": 39,
          "question": "One of the factors that influences decentralization of power in a state is",
          "options": [
            { "optionId": "A", "text": "weak central authority" },
            { "optionId": "B", "text": "widening political participation" },
            { "optionId": "C", "text": "maximum allegiance to the state" },
            { "optionId": "D", "text": "maximum loyalty" }
          ],
          "correctAnswer": "B",
          "explanation": "Decentralization (moving power from the center to local/regional levels) is often done to bring government \'closer to the people,\' allowing more citizens to engage in local decision-making and thus widening political participation."
        },
        {
          "questionId": 40,
          "question": "One of the high points of the activities of the National Congress of British West Africa (NCBWA) led to the",
          "options": [
            { "optionId": "A", "text": "proposal for African unity" },
            { "optionId": "B", "text": "creation of the African High Command" },
            { "optionId": "C", "text": "Africanization of the Executive Council" },
            { "optionId": "D", "text": "introduction of the elective principles" }
          ],
          "correctAnswer": "D",
          "explanation": "A key demand of the NCBWA in the 1920s was for greater African representation in the legislative councils. Their agitation successfully pressured the British to introduce the elective principle (e.g., in the 1922 Clifford Constitution in Nigeria), allowing some Africans to be elected to office for the first time."
        },
        {
          "questionId": 41,
          "question": "The pattern of rule by the French in her West African colonies was highly",
          "options": [
            { "optionId": "A", "text": "decentralized" },
            { "optionId": "B", "text": "proportional" },
            { "optionId": "C", "text": "consultative" },
            { "optionId": "D", "text": "centralized" }
          ],
          "correctAnswer": "D",
          "explanation": "The French policy of assimilation was a direct-rule system, highly centralized and managed from Paris, with little power given to local traditional structures. This is in contrast to the British system of indirect rule."
        },
        {
          "questionId": 42,
          "question": "The Commonwealth of Nations is committed to one of the following principles",
          "options": [
            { "optionId": "A", "text": "human rights and democracy" },
            { "optionId": "B", "text": "supervise political activities of member states" },
            { "optionId": "C", "text": "draft constitution for member states" },
            { "optionId": "D", "text": "finance budget of member states" }
          ],
          "correctAnswer": "A",
          "explanation": "The core values of the Commonwealth, as affirmed in the Harare Declaration, are a commitment to promoting democracy, good governance, human rights, and the rule of law among its members."
        },
        {
          "questionId": 43,
          "question": "The cornerstone of a functioning democracy is the",
          "options": [
            { "optionId": "A", "text": "foreign policy and military alliances" },
            { "optionId": "B", "text": "appropriation bill" },
            { "optionId": "C", "text": "free and fair elections" },
            { "optionId": "D", "text": "expenditure pattern of the executive" }
          ],
          "correctAnswer": "C",
          "explanation": "Democracy is based on the consent of the governed. This consent is given and renewed through periodic free and fair elections, which are the fundamental mechanism for choosing leaders and ensuring accountability."
        },
        {
          "questionId": 44,
          "question": "The issues that are at the core of a country\'s foreign policy are referred to as",
          "options": [
            { "optionId": "A", "text": "globalisation" },
            { "optionId": "B", "text": "state area" },
            { "optionId": "C", "text": "national interest" },
            { "optionId": "D", "text": "diplomacy" }
          ],
          "correctAnswer": "C",
          "explanation": "National interest is the set of goals (e.g., security, economic prosperity, values) that a state seeks to achieve in its interactions with other states. It is the primary driver of all foreign policy decisions."
        },
        {
          "questionId": 45,
          "question": "The underlying principle of the concept of seperation of powers is mainly to",
          "options": [
            { "optionId": "A", "text": "educate people on their rights" },
            { "optionId": "B", "text": "tame the executive" },
            { "optionId": "C", "text": "prevent arbitrariness" },
            { "optionId": "D", "text": "ensure constitutional review" }
          ],
          "correctAnswer": "C",
          "explanation": "By dividing government power among the legislature, executive, and judiciary, the separation of powers (along with checks and balances) ensures that no single branch becomes all-powerful and rules arbitrarily (like a tyrant)."
        },
        {
          "questionId": 46,
          "question": "Secret voting assists the voter in making a decision without",
          "options": [
            { "optionId": "A", "text": "coercion" },
            { "optionId": "B", "text": "pondering over issues" },
            { "optionId": "C", "text": "national interest" },
            { "optionId": "D", "text": "public good" }
          ],
          "correctAnswer": "A",
          "explanation": "The purpose of a secret ballot is to protect the voter from intimidation, bribery, and other forms of pressure (coercion), allowing them to vote their true conscience freely."
        },
        {
          "questionId": 47,
          "question": "Elections plays a major role in the democratic process because, it",
          "options": [
            { "optionId": "A", "text": "ensures only good people rule" },
            { "optionId": "B", "text": "manipulates the electoral process" },
            { "optionId": "C", "text": "confers legitimacy" },
            { "optionId": "D", "text": "projects the class system" }
          ],
          "correctAnswer": "C",
          "explanation": "Legitimacy is the belief that a government has the right to rule. In a democracy, this right is granted by the people through free and fair elections, which confer legitimacy on the winners."
        },
        {
          "questionId": 48,
          "question": "Which one among the following is a significant factor that influences the foreign policy of a country?",
          "options": [
            { "optionId": "A", "text": "Gender of the political leader" },
            { "optionId": "B", "text": "Educational system" },
            { "optionId": "C", "text": "Recruitment policy" },
            { "optionId": "D", "text": "political system" }
          ],
          "correctAnswer": "D",
          "explanation": "A country\'s domestic political system (e.g., democracy, monarchy, autocracy) profoundly shapes its foreign policy goals, its allies, and its methods of interaction with the world."
        },
        {
          "questionId": 49,
          "question": "One of the negative effects of colonial rule on West African states was the",
          "options": [
            { "optionId": "A", "text": "building of roads and rail network" },
            { "optionId": "B", "text": "social interventions" },
            { "optionId": "C", "text": "western education" },
            { "optionId": "D", "text": "dependency syndrome" }
          ],
          "correctAnswer": "D",
          "explanation": "Colonialism restructured West African economies to serve as suppliers of raw materials to Europe. This created a long-term economic and political dependency on their former colonizers that persisted long after independence."
        },
        {
          "questionId": 50,
          "question": "One of the low points in the cabinet system of government is that, the",
          "options": [
            { "optionId": "A", "text": "divided loyalty of the cabinet ministers is strong" },
            { "optionId": "B", "text": "centre of power in the system is stable" },
            { "optionId": "C", "text": "Prime Minister does not regard the Head ofState" },
            { "optionId": "D", "text": "tenure of government is always not stable" }
          ],
          "correctAnswer": "D",
          "explanation": "In a cabinet (parliamentary) system, the government can be dismissed with a \'vote of no confidence\' from the legislature at any time. This can lead to frequent elections and government changes, making the tenure less stable than in a presidential system with fixed terms."
        }
      ]
    }
  ]
}';
// --- END EMBEDDED JSON DATA ---

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions for the Exam View ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    $data = json_decode($jsonContent, true); // Use embedded data

    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format in embedded data']);
        exit();
    }
    
    // Flatten questions, but remove answers/explanations for exam security
    $questions = [];
    foreach ($data['sections'] as $section) {
        foreach ($section['questions'] as $question) {
            // IMPORTANT: Remove sensitive data before sending to client
            unset($question['correctAnswer']);
            unset($question['explanation']);
            $question['sectionName'] = $section['sectionName'];
            $question['sectionId'] = $section['sectionId'];
            $questions[] = $question;
        }
    }
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get All Question Details for Explanation View ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    
    $data = json_decode($jsonContent, true); // Use embedded data

    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format in embedded data']);
        exit();
    }

    // Flatten all questions with all details (correctAnswer and explanation included)
    $questionsWithDetails = [];
    foreach ($data['sections'] as $section) {
        foreach ($section['questions'] as $question) {
            $question['sectionName'] = $section['sectionName'];
            $question['sectionId'] = $section['sectionId'];
            $questionsWithDetails[] = $question;
        }
    }
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];
    
    $data = json_decode($jsonContent, true); // Use embedded data
    
    // Create question map
    $questionMap = [];
    $questionNumberCounter = 1;
    foreach ($data['sections'] as $section) {
        foreach ($section['questions'] as $question) {
            // Use the actual question *number* as the key for comparison, as this is the index used by the JS answers array (1-based)
            $questionMap[$questionNumberCounter] = $question;
            $questionNumberCounter++;
        }
    }
    
    $score = 0;
    $total = count($questionMap);
    
    foreach ($answers as $qNum => $answer) {
        $qNum = intval($qNum);
        if (isset($questionMap[$qNum]) && $answer === $questionMap[$qNum]['correctAnswer']) {
            $score++;
        }
    }
    
    $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => $percentage
    ]);
    exit();
}

// If no action, show the HTML page (The CBT Interface)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Government CBT</title>
    <style>
        

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 40px auto;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
            padding-bottom: 20px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            background: #f7f7f7;
            font-size: 15px;
            border-bottom: 1px solid #eaeaea;
            border-radius: 40px;
            margin: 20px auto;
            width: 90%;
        }

        .steps span {
            flex: 1;
            text-align: center;
            padding: 6px;
            color: #aaa;
        }

        .steps .active {
            color: #007aff;
            font-weight: 600;
        }

        .title {
            text-align: center;
            margin-top: 10px;
        }

        .title h1 {
            font-size: 30px;
            margin: 0;
            color: #1e2a3a;
        }

        .title p {
            margin: 8px 0 22px 0;
            font-size: 15px;
            color: #555;
        }

        .form-box {
            background: linear-gradient(90deg, #4facfe, #43e97b);
            color: white;
            text-align: center;
            padding: 18px 15px;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 25px 30px 25px;
            border-radius: 12px;
        }

        .question-container {
            margin: 0 25px 25px 25px;
            padding: 20px;
            border: 1px solid #e6eaf0;
            border-radius: 12px;
            background: #fafafa;
            min-height: 300px;
        }
        
        /* Style for Explanation Container Questions */
        .explanation-view .question-container {
            background: #ffffff;
            border: 1px solid #cceeff;
            margin-bottom: 20px;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .question-id {
            font-size: 14px;
            color: #007aff;
            font-weight: 600;
        }

        .question-section {
            font-size: 12px;
            color: #999;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .question-text {
            font-size: 17px;
            font-weight: 600;
            color: #243246;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        label.option-label {
            display: block;
            font-size: 15px;
            color: #1e2a3a;
            margin-bottom: 12px;
            cursor: pointer;
            user-select: none;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        /* Hide radio buttons in explanation view */
        .explanation-view input[type="radio"] {
            display: none;
        }
        
        .explanation-view label.option-label {
            cursor: default;
        }
        
        .explanation-view label.option-label:hover {
            background: initial;
            border-color: #e0e0e0;
        }

        label.option-label:hover {
            background: #f0f7ff;
            border-color: #007aff;
        }

        label.option-label input[type="radio"]:checked + span {
            color: #007aff;
            font-weight: 600;
        }

        label.option-label input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
            accent-color: #007aff;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin: 30px 25px;
            gap: 10px;
        }

        .nav-btn {
            background: #43e97b;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-btn:hover:not(.disabled) {
            background: #38c172;
        }

        .nav-btn.disabled {
            background: #b2d6be;
            cursor: default;
            pointer-events: none;
        }

        .question-nav {
            text-align: center;
            margin: 30px 0 40px 0;
            padding: 0 25px;
            overflow-x: auto;
        }

        .question-nav a {
            display: inline-block;
            margin: 0 4px;
            min-width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 50%;
            background: #f7f7f7;
            color: #007aff;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            user-select: none;
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
        }

        .question-nav a.active {
            background: #007aff;
            color: white;
        }

        .question-nav a:hover {
            background: #e6f0ff;
        }

        .question-nav a.answered {
            background: #4caf50;
            color: white;
        }

        .section-divider {
            margin: 30px 25px 0 25px;
            padding: 15px 20px;
            background: #f0f0f0;
            border-left: 4px solid #007aff;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #243246;
        }
        
        .explanation-btn {
            background: #007aff; 
            margin-right: 10px; 
        }
        
        .explanation-btn:hover {
            background: #005bb5; 
        }
        
        .home-btn {
            background: #6c757d; /* Grey color for home */
            margin-right: 10px;
        }

        .home-btn:hover {
            background: #5a6268;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            margin: 20px 25px;
            border-radius: 12px;
            border-left: 4px solid #c62828;
        }
        
        /* Styles for Explanation Highlighting */
        .correct-answer-label {
            border: 2px solid #4CAF50 !important;
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }
        
        .user-answer-label {
            border: 2px solid #FFC107 !important;
            background-color: #fff8e1 !important;
            font-weight: bold;
        }

        .explanation-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #e3f2fd;
            border-left: 5px solid #2196F3;
        }

        .explanation-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .nav-button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
            }
            .nav-button-group .nav-btn {
                 flex: 0 0 auto; /* Stop them from stretching */
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>


    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — Government 2023</h1>
            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <div id="section-display"></div>

        <div class="question-container" id="q-container">
            <p class="loading">Loading questions...</p>
        </div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
            <button class="nav-btn" id="btn-next" disabled>Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script>
    // Get the base URL (e.g., http://example.com/quiz.php) without query parameters
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    
    // Get the path of the current script (e.g., /folder/quiz.php)
    const scriptPath = window.location.pathname;
    // Calculate the home URL by replacing the script name (e.g., quiz.php) with index.php
    // This handles cases where the script is in a subdirectory.
    const homeUrl = scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1) + 'index.php';


    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60; // Default duration is 60 minutes

    let allQuestions = [];
    let answers = {};
    let currentSection = null;
    let explanationData = null; // Store fetched explanation data

    async function loadQuestions() {
        try {
            // The PHP_SELF constant points to the current file
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer();
            } else {
                showError('Failed to load questions: ' + (data.error || 'No questions found.'));
            }
        } catch (error) {
            showError('Error fetching questions: ' + error.message);
        }
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // Section Header Logic
        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Section: ' + qObj.sectionName + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        // The questionId property from the JSON is now used for display, and the array index (current) is used for logic.
        html += '<span class="question-id">Q' + qObj.questionId + '</span>';
        html += '<span class="question-section">' + qObj.sectionName + '</span>';
        html += '</div>';
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            if (answers[current] === opt.optionId) html += 'checked';
            html += ' />';
            html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        // Navigation Button Logic
        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = (current >= allQuestions.length);

        const nav = document.getElementById('nav-buttons');
        const existingSubmit = document.getElementById('btn-submit');
        
        // Display Submit button on the last question
        if (current === allQuestions.length) {
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                nav.appendChild(submitBtn);
            }
            btnNext.style.display = 'none';
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
        }
    }

    function renderNav() {
        let html = '';
        for (let i = 1; i <= allQuestions.length; i++) {
            const answered = answers[i] ? 'answered' : '';
            const active = i === current ? 'active' : '';
            html += '<a href="#" onclick="navigate(' + i + '); return false;" class="' + active + ' ' + answered + '">' + i + '</a>';
        }
        document.getElementById('q-nav').innerHTML = html;
    }
    
    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').addEventListener('click', () => {
        saveAnswer();
        if (current > 1) {
            current--;
            renderQuestion();
            renderNav();
        }
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        saveAnswer();
        if (current < allQuestions.length) {
            current++;
            renderQuestion();
            renderNav();
        }
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form) return;
        // Get the value of the checked radio button
        const selected = form.answer.value || null;
        if (selected) {
            answers[current] = selected; // Store answer by 1-based question number
        } else {
            delete answers[current]; // Remove answer if deselected (though standard CBT doesn't allow deselect)
        }
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    let totalSeconds = duration * 60;
    let timerInterval;

    function updateTimer() {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        document.getElementById('countdown').textContent = str;
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            submitAnswers();
        } else {
            totalSeconds--;
        }
    }

    function startTimer() {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);

        try {
            // Disable buttons to prevent multiple submissions
            document.getElementById('btn-submit').textContent = 'Submitting...';
            document.getElementById('btn-submit').disabled = true;

            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers: answers })
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                showError('Submission failed: ' + result.error);
            }
        } catch (error) {
            showError('Error during submission: ' + error.message);
        }
    }

    function goToHome() {
        // Navigates to index.php in the current directory
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        // Navigates to the current PHP file without query parameters, resetting the quiz
        window.location.href = baseUrl; 
    }

    async function viewExplanations() {
        // Hide results view
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading">Loading explanations...</p>';
        
        if (!explanationData) {
             try {
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_explanations');
                const data = await response.json();
                
                if (data.success) {
                    explanationData = data.questions;
                    displayExplanations();
                } else {
                    explanationContainer.innerHTML = '<div class="error">Failed to load explanation data.</div>';
                }
            } catch (error) {
                explanationContainer.innerHTML = '<div class="error">Error loading explanations: ' + error.message + '</div>';
            }
        } else {
            displayExplanations();
        }
    }

    function displayExplanations() {
        const explanationContainer = document.getElementById('explanation-container');
        let html = '<div class="explanation-view">';

        explanationData.forEach((qObj, index) => {
            const questionNumber = index + 1; // 1-based index from array
            const userAnswer = answers[questionNumber]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + qObj.sectionName + '</span>';
            html += '</div>';
            html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
            
            qObj.options.forEach(opt => {
                let labelClass = '';
                // Highlight the CORRECT answer in GREEN
                if (opt.optionId === qObj.correctAnswer) {
                    labelClass = 'correct-answer-label';
                } 
                // If the user answered and it was NOT the correct answer, highlight the USER's INCORRECT choice in YELLOW
                else if (opt.optionId === userAnswer) {
                    labelClass = 'user-answer-label';
                }

                html += '<label class="option-label ' + labelClass + '">';
                html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
                html += '</label>';
            });

            if (qObj.explanation) {
                html += '<div class="explanation-box"><strong>Explanation:</strong> ' + htmlEscape(qObj.explanation) + '</div>';
            }

            html += '</div>';
        });

        html += '</div>';
        // --- Navigation Group for Explanation View ---
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        html += '<button class="nav-btn" onclick="retakeExam()" style="width: auto;">🔄 Retake Exam</button>';
        html += '</div>';
        // ---------------------------------------------

        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }
    
    // Original displayResults function modified to integrate explanation view and new buttons
    function displayResults(result) {
        document.querySelector('.question-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const container = document.querySelector('.container');
        const resultBox = document.createElement('div');
        resultBox.className = 'question-container';
        resultBox.id = 'results-view'; 
        resultBox.style.textAlign = 'center';
        resultBox.innerHTML = '<div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Results</div>';
        resultBox.innerHTML += '<div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">' + result.score + '/' + result.total + '</div>';
        resultBox.innerHTML += '<div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">' + result.percentage + '%</div>';
        resultBox.innerHTML += '<div style="font-size: 16px; color: #555; margin-bottom: 30px;">You answered ' + result.score + ' out of ' + result.total + ' questions correctly.</div>';
        
        // --- Navigation Group for Results View ---
        resultBox.innerHTML += '<div class="nav-button-group">';
        resultBox.innerHTML += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        resultBox.innerHTML += '<button class="nav-btn explanation-btn" onclick="viewExplanations()">View Explanation</button>';
        resultBox.innerHTML += '<button class="nav-btn" onclick="retakeExam()">🔄 Retake Exam</button>';
        resultBox.innerHTML += '</div>';
        // ----------------------------------------
        
        container.appendChild(resultBox);
    }

    loadQuestions();
</script>



<?php include 'footer2.php'; ?>
</body>
</html>