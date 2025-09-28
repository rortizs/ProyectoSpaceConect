<?php

/**
 * Database Transactions Trait
 *
 * Provides database transaction management for tests.
 * Ensures test isolation by wrapping each test in a transaction.
 */
trait DatabaseTransactions
{
    /**
     * Database connection for transactions
     */
    private $transactionDb;

    /**
     * Transaction started flag
     */
    private bool $transactionStarted = false;

    /**
     * Begin database transaction
     */
    protected function beginDatabaseTransaction(): void
    {
        if ($this->transactionStarted) {
            return;
        }

        try {
            $this->transactionDb = $this->db ?? new Mysql();

            if ($this->transactionDb->conection()) {
                $this->transactionDb->query("START TRANSACTION");
                $this->transactionStarted = true;
            }
        } catch (Exception $e) {
            throw new Exception('Failed to start database transaction: ' . $e->getMessage());
        }
    }

    /**
     * Rollback database transaction
     */
    protected function rollbackDatabaseTransaction(): void
    {
        if (!$this->transactionStarted) {
            return;
        }

        try {
            if ($this->transactionDb) {
                $this->transactionDb->query("ROLLBACK");
                $this->transactionStarted = false;
            }
        } catch (Exception $e) {
            // Log error but don't throw to avoid masking test failures
            error_log('Failed to rollback transaction: ' . $e->getMessage());
        }
    }

    /**
     * Commit database transaction (for special cases)
     */
    protected function commitDatabaseTransaction(): void
    {
        if (!$this->transactionStarted) {
            return;
        }

        try {
            if ($this->transactionDb) {
                $this->transactionDb->query("COMMIT");
                $this->transactionStarted = false;
            }
        } catch (Exception $e) {
            throw new Exception('Failed to commit database transaction: ' . $e->getMessage());
        }
    }

    /**
     * Check if transaction is active
     */
    protected function hasActiveTransaction(): bool
    {
        return $this->transactionStarted;
    }

    /**
     * Execute code within a savepoint
     */
    protected function withSavepoint(callable $callback, string $savepointName = 'test_savepoint')
    {
        if (!$this->transactionStarted) {
            throw new Exception('No active transaction for savepoint');
        }

        try {
            // Create savepoint
            $this->transactionDb->query("SAVEPOINT {$savepointName}");

            // Execute callback
            $result = $callback();

            // If we get here, release the savepoint
            $this->transactionDb->query("RELEASE SAVEPOINT {$savepointName}");

            return $result;
        } catch (Exception $e) {
            // Rollback to savepoint on error
            $this->transactionDb->query("ROLLBACK TO SAVEPOINT {$savepointName}");
            throw $e;
        }
    }

    /**
     * Refresh database connection
     */
    protected function refreshDatabaseConnection(): void
    {
        if ($this->transactionStarted) {
            $this->rollbackDatabaseTransaction();
        }

        // Reinitialize database connection
        $this->transactionDb = new Mysql();
        if ($this->transactionDb->conection()) {
            $this->beginDatabaseTransaction();
        }
    }

    /**
     * Execute multiple queries in a transaction
     */
    protected function executeInTransaction(array $queries): bool
    {
        $originalState = $this->transactionStarted;

        if (!$originalState) {
            $this->beginDatabaseTransaction();
        }

        try {
            foreach ($queries as $query) {
                if (is_array($query)) {
                    [$sql, $params] = $query;
                    if (!$this->transactionDb->prepare($sql, $params)) {
                        throw new Exception("Query failed: {$sql}");
                    }
                } else {
                    if (!$this->transactionDb->query($query)) {
                        throw new Exception("Query failed: {$query}");
                    }
                }
            }

            if (!$originalState) {
                $this->commitDatabaseTransaction();
            }

            return true;
        } catch (Exception $e) {
            if (!$originalState) {
                $this->rollbackDatabaseTransaction();
            }
            throw $e;
        }
    }
}