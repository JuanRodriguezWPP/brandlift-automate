<?php

$content = file_get_contents('app/Http/Controllers/BrandliftController.php');

$method = <<< 'TEXT'

    /**
     * API: Update creatives and questions (and log changes).
     */
    public function updateCreatives(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'study_id' => 'required|integer',
            'creatives' => 'required|array',
            'creatives.*.question_number' => 'required|integer',
            'creatives.*.variant_key' => 'required|string',
            'creatives.*.html' => 'required|string',
            'questions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $study = BrandliftStudy::with('questions', 'creatives')->findOrFail($request->input('study_id'));
            
            $oldQuestions = $study->questions->map(function($q) {
                return ['question' => $q->question_text, 'answers' => $q->answers];
            })->toArray();

            // Update creatives
            foreach ($request->input('creatives') as $cData) {
                $creative = $study->creatives()
                    ->where('question_number', $cData['question_number'])
                    ->where('variant_key', $cData['variant_key'])
                    ->first();
                
                if ($creative) {
                    $creative->creative_html = $cData['html'];
                    $creative->save();
                }
            }

            // Update questions
            // First, delete existing questions
            $study->questions()->delete();

            // Insert new questions
            $newQuestions = $request->input('questions');
            foreach ($newQuestions as $idx => $qData) {
                $study->questions()->create([
                    'question_number' => $idx + 1,
                    'question_text' => $qData['text'],
                    'answers' => $qData['answers'],
                    'creative_html' => $request->input('creatives')[0]['html'] ?? '', // Fallback
                ]);
            }

            // Also update the question_count
            $study->question_count = count($newQuestions);
            $study->save();

            // Record Edit Log
            \App\Models\BrandliftEditLog::create([
                'brandlift_study_id' => $study->id,
                'user_id' => auth()->id(),
                'changes_made' => [
                    'old_questions' => $oldQuestions,
                    'new_questions' => $newQuestions,
                ]
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Creativos actualizados exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando creativos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
        }
    }
}
TEXT;

$content = str_replace("}\n", $method, $content);
file_put_contents('app/Http/Controllers/BrandliftController.php', $content);
echo "Method added.\n";
