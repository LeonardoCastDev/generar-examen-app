import { Document, Page, Text, View, StyleSheet } from "@react-pdf/renderer";
import type { Exam, Question } from "@types/domain";
import { todayISO } from "@utils/date";

const styles = StyleSheet.create({
  page: { padding: 32 },
  header: {
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    marginBottom: 16,
  },
  title: { fontSize: 18, fontWeight: 700 },
  meta: { fontSize: 10 },
  q: { marginBottom: 12 },
  qText: { fontSize: 12, marginBottom: 6 },
  option: { fontSize: 11, marginLeft: 8 },
});

export default function ExamDocument({
  exam,
  selection,
  teacherKey,
}: {
  exam: Exam;
  selection: Question[];
  teacherKey: boolean;
}) {
  return (
    <Document>
      <Page size="A4" style={styles.page}>
        <View style={styles.header}>
          <Text style={styles.title}>{exam.title}</Text>
          <View>
            <Text style={styles.meta}>Alumno: __________________</Text>
            <Text style={styles.meta}>Matrícula: ________________</Text>
            <Text style={styles.meta}>Fecha: {todayISO()}</Text>
          </View>
        </View>
        {selection.map((q, idx) => (
          <View key={q.id} style={styles.q}>
            <Text style={styles.qText}>
              {idx + 1}. {q.question_text}
            </Text>
            {q.type === "multiple" &&
              q.options?.map((o, i) => (
                <Text key={i} style={styles.option}>
                  {String.fromCharCode(65 + i)}) {o.option_text}
                  {teacherKey && o.is_correct ? " ✓" : ""}
                </Text>
              ))}
          </View>
        ))}
      </Page>
    </Document>
  );
}
