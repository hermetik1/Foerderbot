<?php
/**
 * Sample data seeder for KI Kraft plugin.
 * Run this to populate the knowledge base with sample FAQ entries.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed sample FAQ data.
 */
function ki_kraft_seed_sample_data() {
	$sample_faqs = array(
		array(
			'title'   => 'Wie gründe ich einen Verein?',
			'content' => 'Um einen Verein zu gründen, benötigen Sie mindestens sieben Gründungsmitglieder. Diese müssen eine Satzung erstellen, die den Namen, Zweck, Sitz und weitere Details des Vereins festlegt. Anschließend wird eine Gründungsversammlung abgehalten, bei der die Satzung beschlossen und ein Vorstand gewählt wird. Der Verein muss dann beim zuständigen Amtsgericht ins Vereinsregister eingetragen werden.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Welche Förderungen gibt es für Vereine?',
			'content' => 'Es gibt verschiedene Fördermöglichkeiten für Vereine: Bundes- und Landesförderprogramme, kommunale Zuschüsse, Stiftungsgelder und EU-Fördermittel. Die Förderungen können für verschiedene Bereiche wie Jugendarbeit, Kultur, Sport, Soziales oder Umweltschutz beantragt werden. Voraussetzung ist meist ein gemeinnütziger Zweck und die Einhaltung bestimmter Kriterien.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Wie beantrage ich eine Gemeinnützigkeit?',
			'content' => 'Die Gemeinnützigkeit wird vom Finanzamt anerkannt. Dazu muss die Satzung des Vereins gemeinnützige Zwecke nach §52 AO verfolgen. Diese können z.B. sein: Förderung von Wissenschaft, Bildung, Kunst, Kultur, Sport, Umweltschutz oder Unterstützung hilfsbedürftiger Personen. Der Antrag wird beim zuständigen Finanzamt gestellt, das dann einen Freistellungsbescheid ausstellt.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Was ist eine Mitgliederversammlung?',
			'content' => 'Die Mitgliederversammlung ist das oberste Organ eines Vereins. Sie entscheidet über grundlegende Fragen wie Satzungsänderungen, Wahl und Entlastung des Vorstands, Jahresabschluss und wichtige Vereinsangelegenheiten. Jedes Mitglied hat in der Regel eine Stimme. Die Einladung muss fristgerecht erfolgen und die Beschlüsse werden protokolliert.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Welche Versicherungen braucht ein Verein?',
			'content' => 'Wichtige Versicherungen für Vereine sind: Vereinshaftpflichtversicherung (deckt Schäden durch Vereinsaktivitäten ab), Veranstalterhaftpflicht (für Events), Vermögensschaden-Haftpflicht (für den Vorstand), Unfallversicherung (für ehrenamtliche Helfer) und ggf. eine Rechtsschutzversicherung. Der konkrete Bedarf hängt von den Aktivitäten des Vereins ab.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Wie erstelle ich einen Förderantrag?',
			'content' => 'Ein Förderantrag sollte folgende Elemente enthalten: 1) Projektbeschreibung mit Zielen und Zielgruppe, 2) Detaillierter Kosten- und Finanzierungsplan, 3) Zeitplan und Meilensteine, 4) Nachweis der Gemeinnützigkeit, 5) Vereinsunterlagen (Satzung, Vorstandsbeschluss), 6) Ggf. Referenzen und Erfahrungsnachweise. Achten Sie auf die spezifischen Anforderungen des jeweiligen Förderprogramms.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Was muss in einer Vereinssatzung stehen?',
			'content' => 'Eine Vereinssatzung muss nach §57 BGB folgende Mindestangaben enthalten: Name und Sitz des Vereins, Zweck des Vereins, Bestimmungen über Eintritt und Austritt von Mitgliedern, Regelungen zur Mitgliedsbeiträge, Bildung des Vorstands, Voraussetzungen für Beschlussfassungen, Bestimmungen zur Auflösung des Vereins. Für gemeinnützige Vereine gelten zusätzliche Anforderungen nach der Abgabenordnung.',
			'scope'   => 'public',
		),
		array(
			'title'   => 'Wie wird man Mitglied?',
			'content' => 'Die Mitgliedschaft beginnt in der Regel mit einem schriftlichen Aufnahmeantrag, der an den Vorstand gerichtet wird. Der Vorstand entscheidet über die Aufnahme gemäß den Regelungen in der Satzung. Nach der Annahme wird ein Mitgliedsbeitrag fällig. Details zum Aufnahmeverfahren, zu Rechten und Pflichten der Mitglieder sowie zur Beitragshöhe finden sich in der Vereinssatzung.',
			'scope'   => 'public',
		),
	);

	foreach ( $sample_faqs as $faq ) {
		KI_Kraft_FAQ::add_entry( $faq['title'], $faq['content'], $faq['scope'] );
	}

	return count( $sample_faqs );
}


