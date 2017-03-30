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

x = tf.placeholder(tf.float32, [None, 3])

W1 = weight_variable([3, 500])
b1 = bias_variable([500])

x_1 = tf.nn.relu(tf.matmul(x, W1) + b1)

W2 = weight_variable([500, 250])
b2 = bias_variable([250])

x_2 = tf.nn.relu(tf.matmul(x_1, W2)+b2)

W3 = weight_variable([250, 1])
b3 = bias_variable([1])

y = tf.matmul(x_2, W3) + b3
y_ = tf.placeholder(tf.float32, [None, 1])


loss = tf.contrib.losses.squared(y_, y)
train_step = tf.train.GradientDescentOptimizer(0.005).minimize(loss)
#correct_prediction = tf.equal(tf.argmax(y,1), tf.argmax(y_,1))
#accuracy = tf.reduce_mean(tf.cast(correct_prediction, tf.float32))
init = tf.initialize_all_variables()
#sess.run(tf.initialize_all_variables())


print "Teaching the Network"

#i think everything up is right?
#not sure how to run on our data. check below
# for i in range(1000):
#   batch = credit.train.next_batch(200)
#   sess.run(train_step, feed_dict={x: batch[0], y_: batch[1]})
with tf.Session() as sess:
    sess.run(init)
    for i in range(252):
        batch_xs, batch_ys = credit.train.next_batch(1)
        #print batch_xs
        #print batch_ys
        batch_ys = np.reshape(batch_ys, [-1, 1])
        #if i%100 == 0:
        #train_accuracy = accuracy.eval(feed_dict={x:batch_xs, y_: batch_ys})
        # acc = sess.run(accuracy, feed_dict={x: credit.test.input, y_: credit.test.labels})

        # Display logs per epoch step
        # c = sess.run(loss, feed_dict={x:batch_xs, y_: batch_ys})
        # print ("loss is ", "{:.9f}".format(c))

        sess.run(train_step, feed_dict={x:batch_xs, y_: batch_ys})

        output = tf.sub(y, 0)
        print i
        print sess.run(y,1, feed_dict={x:batch_xs, y_: batch_ys})

        #print("step %d, batch training accuracy %g"%(i, train_accuracy))
        #print(sess.run(tf.argmax(y,1), feed_dict={x:batch_xs}))
        #_, w_out, b_out= sess.run([train_step, W3, b], feed_dict={x: batch_xs, y_: batch_ys})
        #print (w_out)
        # trainacc = sess.run(accuracy, feed_dict={x: credit.train.input, y_: credit.train.labels})
        #print("step %d, whole training accuracy %g"%(i, trainacc))

        #train_step.run(feed_dict={x: batch_xs, y_: batch_ys})

        # print("Epoch:", '%04d' % (i), "cost=", "{:.9f}".format(c), \
        #     "W=", sess.run(W3), "b=", sess.run(b3))

    #sess.run(accuracy, feed_dict={x: credit.train.input, y_: credit.train.labels})
    #correct_prediction = tf.equal(tf.argmax(y,1), tf.argmax(y_,1))
    #accuracy = tf.reduce_mean(tf.cast(correct_prediction, tf.float32))
    #print "Test Accuracy"
    #print sess.run(accuracy, feed_dict={x: credit.test.input, y_: credit.test.labels})

    print"Saving the Model"
    saver = tf.train.Saver()
    saver.save(sess, 'saved-model')
    print"Model saved to 'saved-model.meta'"
    sess.close()

# #all further code is used to load the saved model and run the testing data again
# print"Loading saved model and starting new session"
# with tf.Session() as sess:
#     sess.run(tf.initialize_all_variables())
#     saver.restore(sess, tf.train.latest_checkpoint('./'))
#     print("Model restored running testing data again")
#     sess.run(accuracy, feed_dict={x: credit.train.input, y_: credit.train.labels})
#     correct_prediction = tf.equal(tf.argmax(y,1), tf.argmax(y_,1))
#     accuracy = tf.reduce_mean(tf.cast(correct_prediction, tf.float32))
#     print "Test Accuracy round 2"
#     print sess.run(accuracy, feed_dict={x: credit.test.input, y_: credit.test.labels})
