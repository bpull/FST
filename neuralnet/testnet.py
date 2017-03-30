from __future__ import absolute_import
from __future__ import division
import tensorflow as tf
import numpy as np
import csv
from six.moves import urllib
from six.moves import xrange  # pylint: disable=redefined-builtin
import os
import tempfile


class DataSet(object):

  def __init__(self, input, labels, dtype=tf.float32):
    """Construct a DataSet."""

    self._input = input
    self._labels = labels
    self._epochs_completed = 0
    self._index_in_epoch = 0
    self._num_examples = 252

  @property
  def input(self):
    return self._input

  @property
  def labels(self):
    return self._labels

  @property
  def num_examples(self):
    return self._num_examples

  @property
  def epochs_completed(self):
    return self._epochs_completed

  def next_batch(self, batch_size):
    """Return the next `batch_size` examples from this data set."""
    start = self._index_in_epoch
    self._index_in_epoch += batch_size
    if self._index_in_epoch > self._num_examples:
      # Finished epoch
      self._epochs_completed += 1
      # Shuffle the data
      perm = np.arange(self._num_examples)
      np.random.shuffle(perm)
      self._input = self._input[perm]
      self._labels = self._labels[perm]
      # Start next epoch
      start = 0
      self._index_in_epoch = batch_size
      assert batch_size <= self._num_examples
    end = self._index_in_epoch
    return self._input[start:end], self._labels[start:end]


def read_data_sets(dtype=tf.float32):
    print "Begin Reading Data"
    class DataSets(object):
        pass
    data_sets = DataSets()

    train_input = []
    train_labels = []
    test_input = []
    test_labels = []
    count = 0

    filename_queue = tf.train.string_input_producer(["ko.csv"])

    reader = tf.TextLineReader()
    key, value = reader.read(filename_queue)

    default_values = [tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32)]
    volatile, end1, end2, daychange = tf.decode_csv(value, record_defaults=default_values)
    features = tf.pack([volatile, end1, end2])
    daychange = tf.pack([daychange])

    print "Starting to load train data"

    with tf.Session() as sess:
    # Start populating the filename queue.
        coord = tf.train.Coordinator()
        threads = tf.train.start_queue_runners(coord=coord)

        for i in range(252):
            # Retrieve a single instance:
            example, label = sess.run([features, daychange])
            train_input.append(example)
            train_labels.append(label)

        coord.request_stop()
        coord.join(threads)

    filename_queue = tf.train.string_input_producer(["ko.csv"])

    reader = tf.TextLineReader()
    key, value = reader.read(filename_queue)

    default_values = [tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32)]
    volatile, end1, end2, daychange = tf.decode_csv(value, record_defaults=default_values)
    features = tf.pack([volatile, end1, end2])
    daychange = tf.pack([daychange])

    print "Starting to load test data"

    with tf.Session() as sess:
    # Start populating the filename queue.
        coord = tf.train.Coordinator()
        threads = tf.train.start_queue_runners(coord=coord)

        for i in range(252):
        # Retrieve a single instance:
            example, label = sess.run([features, daychange])
            test_input.append(example)
            test_labels.append(label)

        coord.request_stop()
        coord.join(threads)

    train_input = np.array(train_input)
    train_labels = np.array(train_labels)
    test_input = np.array(test_input)
    test_labels = np.array(test_labels)

    data_sets.train = DataSet(train_input, train_labels, dtype=dtype)
    data_sets.test = DataSet(test_input, test_labels, dtype=dtype)

    return data_sets

def weight_variable(shape):
  initial = tf.truncated_normal(shape, stddev=0.1)
  return tf.Variable(initial)

def bias_variable(shape):
  initial = tf.constant(0.1, shape=shape)
  return tf.Variable(initial)

credit = read_data_sets()
#sess = tf.InteractiveSession()

print "Data Successfully Read In"
print "Starting to learn Neural Network Structure"

'''
A logistic regression learning algorithm example using TensorFlow library.
This example is using the MNIST database of handwritten digits
(http://yann.lecun.com/exdb/mnist/)
Author: Aymeric Damien
Project: https://github.com/aymericdamien/TensorFlow-Examples/
'''

# Parameters
learning_rate = 0.01
training_epochs = 4
batch_size = 63
display_step = 1

# tf Graph Input
x = tf.placeholder(tf.float32, [None, 3]) # mnist data image of shape 28*28=784
y = tf.placeholder(tf.float32, [None, 1]) # 1

# Set model weights
W = tf.Variable(tf.zeros([3, 1]))
b = tf.Variable(tf.zeros([1]))

# Construct model
pred = tf.nn.softmax(tf.matmul(x, W) + b) # Softmax

# Minimize error using cross entropy
cost = tf.reduce_mean(-tf.reduce_sum(y*tf.log(pred), reduction_indices=1))
# Gradient Descent
optimizer = tf.train.GradientDescentOptimizer(learning_rate).minimize(cost)

# Initializing the variables
init = tf.initialize_all_variables()

# Launch the graph
with tf.Session() as sess:
    sess.run(init)

    # Training cycle
    for epoch in range(training_epochs):
        avg_cost = 0.
        total_batch = 4
        # Loop over all batches
        for i in range(total_batch):
            batch_xs, batch_ys = credit.train.next_batch(batch_size)
            # Run optimization op (backprop) and cost op (to get loss value)
            _, c = sess.run([optimizer, cost], feed_dict={x: batch_xs,
                                                          y: batch_ys})
            # Compute average loss
            avg_cost += c / total_batch
        # Display logs per epoch step
        if (epoch+1) % display_step == 0:
            print("Epoch:", '%04d' % (epoch+1), "cost=", "{:.9f}".format(avg_cost))

    print("Optimization Finished!")

    # Test model
    correct_prediction = tf.equal(tf.argmax(pred, 1), tf.argmax(y, 1))
    # Calculate accuracy
    accuracy = tf.reduce_mean(tf.cast(correct_prediction, tf.float32))
    print("Accuracy:", accuracy.eval({x: credit.test.input, y: credit.test.labels}))
