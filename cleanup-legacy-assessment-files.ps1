$legacyFiles = @(
    'app\Http\Controllers\AIQuizController.php',
    'app\Http\Controllers\AnswerOptionController.php',
    'app\Http\Controllers\AptitudeScoreMappingController.php',
    'app\Http\Controllers\AssessmentController.php',
    'app\Http\Controllers\QuestionController.php',
    'app\Http\Controllers\UserAnswerController.php',
    'app\Http\Controllers\UserTestAttemptController.php',
    'app\Http\Resources\AnswerOptionResource.php',
    'app\Http\Resources\AptitudeScoreMappingResource.php',
    'app\Http\Resources\AssessmentResource.php',
    'app\Http\Resources\QuestionResource.php',
    'app\Http\Resources\UserAnswerResource.php',
    'app\Http\Resources\UserTestAttemptResource.php',
    'app\Models\AptitudeScoreMapping.php',
    'app\Models\Placement.php'
)

foreach ($relativePath in $legacyFiles) {
    $fullPath = Join-Path $PSScriptRoot $relativePath

    if (Test-Path -LiteralPath $fullPath) {
        Remove-Item -LiteralPath $fullPath
        Write-Host "Removed: $relativePath"
    }
}

Write-Host 'Legacy assessment cleanup completed.'
