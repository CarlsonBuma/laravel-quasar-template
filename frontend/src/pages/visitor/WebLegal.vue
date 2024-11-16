<template>

    <PageWrapper noMargin>
        <SectionDesignClear>
            <SectionSplit>
                <template #left>
                    <SectionTitle 
                        class="w-title"
                        header="*by using our services, you are agreeing with our Legal &amp; Compliance policies."
                        title="Information about our Legal &amp; Compliance policies."
                        subtitle="
                            Collabris provides a digital plattform and services, that connect collaborators within our community.
                            We recommend, reading our Terms &amp; Conditions carefully, so you know how we are collaborating with you.
                            See our informations:
                        "
                    >
                        <ul class="text-subtitle1">
                            <li>Terms &amp; Conditions</li>
                            <li>Data protection policies</li>
                        </ul>
                    </SectionTitle>
                </template>
                <template #right>
                    <CardFocus 
                        class="w-card-lg"
                        icon="business" 
                        title="Impressum" 
                        description="This plattform is provided by:"
                    >
                        <span>
                            Some Template<br>
                            Some Creditss<br>
                            CH-8000 Zurich<br>
                        </span>
                        <template #note>
                            <p class="q-mb-none">
                                <span>Contact us: <a :href="'mailto:' + redirects.emailLegal">{{ redirects.emailLegal }}</a></span>
                            </p>
                        </template>
                    </CardFocus>
                </template>
            </SectionSplit>
        </SectionDesignClear>

        <!-- Legal & Compliance -->
        <SectionDesignColored>
            <SectionTitle 
                class="text-center text-white"
                title="Terms &amp; Conditions."
                subtitle="Before you start using our services, please read our Terms &amp; Conditions carefully."
            />
            <CardSimple 
                class="w-card-md"
                v-for="(term, index) in disclaimers"
                :key="index"
                :title="term.title" 
            >
                <q-card-section >
                    <div class="flex justify-center">
                        <q-icon name="policy" size="72px" color="primary" />
                    </div>
                    <p class="q-mt-md text-start">{{ term.description }}</p>
                </q-card-section>
            </CardSimple>
        </SectionDesignColored>

        <!-- Data protection -->
        <SectionDesignDefault>
            <SectionTitle 
                class="text-center"
                title="Data protection policies."
                subtitle="Here we declare, how we are colleting and processing your data."
            />
            <CardSimple 
                class="w-card-md"
                v-for="(term, index) in disclaimers"
                :key="index"
                :title="term.title" 
            >
                <q-card-section >
                    <div class="flex justify-center">
                        <q-icon name="policy" size="72px" color="primary" />
                    </div>
                    <p class="q-mt-md text-start">{{ term.description }}</p>
                </q-card-section>
            </CardSimple>
        </SectionDesignDefault>
    </PageWrapper>

</template>

<script>
import { redirects } from 'boot/globals.js'
import CardFocus from 'components/CardFocus.vue';

export default {
    name: 'WebLegal',
    components: {
        CardFocus
    },

    setup() {
        const disclaimers = [
            {
                id: 1,
                title: 'Rechtliche Hinweise',
                description: 'Mit dem Zugriff auf die Webseite von Gigup Solutions GmbH erklärst du, dass du die nachstehenden Anwendungsbedingungen verstanden hast und anerkennst. Wir halten uns das Recht vor, Änderungen an unseren Bestimmungen transparent vorzunehmen.'
            },
            {
                id: 2,
                title: 'Unsere Services',
                description: 'Gigup Solutions GmbH bietet Dienstleistungen im Bereich Beratung (Gigup Consulting), Softwareentwicklung & Bereitstellen agiler Teams (Gigup Solutions) und Etablierung (Gigup Establishment) an. Unabhängig betreibt Gigup Solutions GmbH seine eigenen Webservices (Gigup Plattform) und stellt diese seiner Community zur Verfügung.',
            },
            {
                id: 3,
                title: 'Unser Versprechen',
                description: 'Kompetente Beratung, Bündelung von Knowhow, sowie kosteneffektives und modulares Entwickeln von Softwarelösungen. Wir unterstützen dich bei der Realisierung deiner Vision. Wir finden eine Lösung die dich überzeugt! Unser Service ist transparent und fair.'
            },
            {
                id: 4,
                title: 'Haftungsschluss: Services',
                description: 'Ab Zeitpunkt des Projektabschlusses, entfällt die Verantwortung für den sicheren und rechtskonformen Betrieb des digitalen Produkts auf den Projektinhaber. Gigup Solutions GmbH übernimmt keine Garantie zum erfolgreichen Betrieb von Applikationen und Infrastrukturen.'
            },
            {
                id: 5,
                title: 'Haftungsschluss: Gigup Plattform',
                description: 'Die Benutzung der dir zur Verfügung gestellten Webservices & Infrastrukturen durch Gigup Solutions GmbH obliegt in deiner Verantwortung. Wir übernehmen keine Haftung für jegliche Schäden, hervorgerufen durch die Nutzung unserer Plattform/en.'
            },
            {
                id: 6,
                title: 'GDPR Compliance',
                description: 'Es kommt vor, dass wir einige Informationen über dich sammeln (z.B. Google Analytics, Session Cookies, etc.). Deine Daten werden ausschliesslich für die Verbesserung unserer Services verwertet und erfolgen Nutzungsbedingt. Wir richten uns nach dem DSG - Schweiz.'
            },
            {
                id: 7,
                title: 'Datenerfassung',
                description: 'Bei der Benutzung unserer Webservices und Apps werden Informationen verarbeitet, um einen nutzbaren Dienst zu gewährleisten. Darunter gehören u.a. Informationen welche du uns zukommen lässt und anonymisierte Browserdaten.'
            },
            {
                id: 8,
                title: 'Datenverarbeitung',
                description: 'Die Datenverarbeitung erfolgt über unseren dedicated Server Provider (https://www.firestorm.ch) in der Schweiz. Es werden nur Daten und Informationen verarbeitet, die du uns explizit mitgeteilt hast. Wir geben keine Informationen an Drittpersonen weiter.'
            },
            {
                id: 9,
                title: 'Datensicherheit',
                description: 'Wir führen regelmässige Audits und Tests durch, um einen hohen Sicherheitsstandard zu gewährleisten. Sollte durch Fremdeinwirkung Daten abhanden kommen, wird jegliche Haftung abgelehnt, sowie nötige Schritte eingeleitet.'
            },
            {
                id: 10,
                title: 'Urheberrecht',
                description: 'Die Marke, das Logo, das Design und alle dazugehörigen Komponenten (Business Modelle, Apps, usw.) sind Urheberrechtlich durch Gigup Solutions GmbH geschützt. Externe Quellen wie z.B. Illustrationen, Design, etc. sind lizenziert und dürfen nicht wiederverwednet werden. Bitte versuche nicht uns zu kopieren.'
            },
            {
                id: 11,
                title: 'Aktualität und Vollständigkeit',
                description: 'Gigup Solutions GmbH hat die Informationen nach bestem Wissen und Gewissen zusammengestellt. Dennoch kann keine Garantie für die Aktualität, Voll­ständig­keit und Korrektheit der bereit­gestellten Angaben über­nommen werden. Sobald uns Fehler bekannt werden, werden wir die Inhalte korrigieren.'
            },
            {
                id: 12,
                title: 'Rechte und Pflichten',
                description: 'Gigup Solutions GmbH verpflichtet sich Auskunft über die Datenerhebung für den jeweiligen Benutzer zu geben. Wird eine Verletzung der Datensicherheit / Policies bei Gigup Solutions GmbH festgestellt, so besteht die Pflicht auf das Problem hinzuweisen. Dies muss schriftlich über hello@gigup.ch erfolgen.'
            },
            {
                id: 13,
                title: 'Gerichtsstand',
                description: 'Für alle Streitigkeiten, die sich aus oder im Zusammenhang mit dieser Website oder den darauf angebotenen Produkten oder Dienstleistungen ergeben, gilt ausschliesslich schweizerisches Recht. Gerichtsstand ist Bern.'
            },
        ];
        return {
            redirects,
            disclaimers
        }
    },

    data () {
        return {
            // 
        }
    }
};
</script>
